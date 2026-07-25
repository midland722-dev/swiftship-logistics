<?php
/**
 * Lightweight database-backed job queue.
 *
 * Keeps tracking-side work (email, webhooks, notifications) off the
 * request path so writes stay fast and failures are retryable. A worker
 * (admin/worker.php) drains the queue. No external dependencies.
 */

if (!defined('QUEUE_MAX_ATTEMPTS')) {
    define('QUEUE_MAX_ATTEMPTS', 5);
}

function ensureJobsTable(PDO $db) {
    try {
        $db->query("SELECT 1 FROM jobs LIMIT 1");
    } catch (Exception $e) {
        $db->exec("
            CREATE TABLE IF NOT EXISTS jobs (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                queue varchar(50) NOT NULL,
                payload longtext NOT NULL,
                attempts int(11) DEFAULT 0,
                status enum('pending','processing','done','failed') DEFAULT 'pending',
                available_at datetime DEFAULT CURRENT_TIMESTAMP,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                processed_at datetime NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY idx_queue_status (queue, status, available_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}

/**
 * Enqueue a job. $payload is any JSON-serializable value.
 * $delaySec lets a job wait (e.g. backoff) before becoming available.
 */
function enqueueJob(PDO $db, $queue, $payload, $delaySec = 0) {
    ensureJobsTable($db);
    $delaySec = (int)$delaySec;
    if ($delaySec > 0) {
        $db->prepare("
            INSERT INTO jobs (queue, payload, available_at, created_at)
            VALUES (:queue, :payload, DATE_ADD(NOW(), INTERVAL :d SECOND), NOW())
        ")->execute([
            ':queue'   => $queue,
            ':payload' => json_encode($payload),
            ':d'       => $delaySec,
        ]);
    } else {
        $db->prepare("
            INSERT INTO jobs (queue, payload, available_at, created_at)
            VALUES (:queue, :payload, NOW(), NOW())
        ")->execute([
            ':queue'   => $queue,
            ':payload' => json_encode($payload),
        ]);
    }
    return (int)$db->lastInsertId();
}

/**
 * Process up to $limit jobs for the given $handlers (queue => callable).
 * Each handler receives ($db, $payload) and should throw on failure.
 * Returns counts processed.
 */
function processQueue(PDO $db, array $handlers, $limit = 50) {
    ensureJobsTable($db);
    $jobs = $db->query("SELECT * FROM jobs WHERE status='pending' AND available_at <= NOW() ORDER BY id ASC LIMIT " . (int)$limit)->fetchAll(PDO::FETCH_ASSOC);

    $counts = ['done' => 0, 'failed' => 0, 'skipped' => 0];
    foreach ($jobs as $job) {
        $queue = $job['queue'];
        if (!isset($handlers[$queue])) {
            // No handler registered; mark done to avoid reprocessing.
            $db->prepare("UPDATE jobs SET status='done', processed_at=NOW() WHERE id=:id")
               ->execute([':id' => $job['id']]);
            $counts['skipped']++;
            continue;
        }
        $payload = json_decode($job['payload'], true) ?: [];
        // Atomic claim: only the worker that flips status to 'processing' proceeds.
        $aff = $db->prepare("UPDATE jobs SET status='processing', attempts = attempts + 1 WHERE id=:id AND status='pending'")
                   ->execute([':id' => $job['id']]);
        if ($aff == 0) {
            continue;
        }
        try {
            $handlers[$queue]($db, $payload);
            $db->prepare("UPDATE jobs SET status='done', processed_at=NOW() WHERE id=:id")
               ->execute([':id' => $job['id']]);
            $counts['done']++;
        } catch (Exception $e) {
            $attempts = (int)$job['attempts'] + 1;
            if ($attempts >= QUEUE_MAX_ATTEMPTS) {
                $db->prepare("UPDATE jobs SET status='failed', processed_at=NOW() WHERE id=:id")
                   ->execute([':id' => $job['id']]);
                $counts['failed']++;
            } else {
                $backoff = min(300, 10 * $attempts);
                $db->prepare("UPDATE jobs SET status='pending', available_at = DATE_ADD(NOW(), INTERVAL :b SECOND) WHERE id=:id")
                   ->execute([':b' => $backoff, ':id' => $job['id']]);
            }
            getLogger()->error("Job #{$job['id']} ({$queue}) failed: " . $e->getMessage());
        }
    }
    return $counts;
}
