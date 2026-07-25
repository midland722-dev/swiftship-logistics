<?php
/**
 * ASCL Logistics - Application Logger
 * Centralized error and operation logging system
 * 
 * Usage:
 *   $logger = new Logger();
 *   $logger->error('Database connection failed', ['db_host' => $host, 'error' => $e->getMessage()]);
 *   $logger->warning('Low disk space', ['path' => $path, 'free_space' => $free]);
 *   $logger->info('Shipment created', ['tracking' => $tracking, 'id' => $shipment_id]);
 */

class Logger {
    private $log_dir;
    private $log_files = [];
    private $max_file_size = 10 * 1024 * 1024; // 10MB per log file
    private $max_files = 5; // Keep max 5 rotated files
    
    public function __construct($log_dir = null) {
        $this->log_dir = $log_dir ?? __DIR__ . '/../../uploads/logs/';
        
        if (!file_exists($this->log_dir)) {
            @mkdir($this->log_dir, 0755, true);
        }
        
        if (!is_writable($this->log_dir)) {
            @chmod($this->log_dir, 0755);
        }
    }
    
    /**
     * Log an error message
     */
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Log a warning message
     */
    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Log an info message
     */
    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Log a debug message
     */
    public function debug($message, $context = []) {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * Core logging method
     */
    private function log($level, $message, $context = []) {
        $log_file = $this->getLogFile('application');
        
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'user_id' => $_SESSION['admin_id'] ?? null,
            'user_name' => $_SESSION['admin_username'] ?? 'guest',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'cli',
            'url' => $_SERVER['REQUEST_URI'] ?? 'cli'
        ];
        
        $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        
        if (file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX) === false) {
            error_log("Failed to write to log file: $log_file");
        }
        
        $this->rotateLog($log_file);
    }
    
    /**
     * Log database errors specifically
     */
    public function database($message, $sql = null, $error = null) {
        $context = [];
        if ($sql) $context['sql'] = $sql;
        if ($error) $context['error'] = $error;
        $this->log('DATABASE', $message, $context);
    }
    
    /**
     * Log authentication events
     */
    public function auth($message, $username = null, $success = null) {
        $context = [];
        if ($username) $context['username'] = $username;
        if ($success !== null) $context['success'] = $success ? 1 : 0;
        $this->log('AUTH', $message, $context);
    }
    
    /**
     * Log shipment operations
     */
    public function shipment($message, $tracking_number = null, $action = null) {
        $context = [];
        if ($tracking_number) $context['tracking_number'] = $tracking_number;
        if ($action) $context['action'] = $action;
        $this->log('SHIPMENT', $message, $context);
    }
    
    /**
     * Log payment operations
     */
    public function payment($message, $payment_id = null, $amount = null) {
        $context = [];
        if ($payment_id) $context['payment_id'] = $payment_id;
        if ($amount !== null) $context['amount'] = $amount;
        $this->log('PAYMENT', $message, $context);
    }
    
    /**
     * Get log file path with rotation
     */
    private function getLogFile($type) {
        $date = date('Y-m-d');
        $filename = sprintf('%s_%s.log', $type, $date);
        return $this->log_dir . $filename;
    }
    
    /**
     * Rotate log files when they exceed max size
     */
    private function rotateLog($log_file) {
        if (!file_exists($log_file)) return;
        
        $size = filesize($log_file);
        if ($size < $this->max_file_size) return;
        
        for ($i = $this->max_files - 1; $i >= 1; $i--) {
            $old_file = $log_file . '.' . $i;
            $new_file = $log_file . '.' . ($i + 1);
            
            if (file_exists($old_file)) {
                @rename($old_file, $new_file);
            }
        }
        
        @rename($log_file, $log_file . '.1');
    }
    
    /**
     * Read logs with optional filtering
     */
    public function read($type = 'application', $date = null, $level = null, $limit = 100) {
        $date = $date ?? date('Y-m-d');
        $log_file = $this->log_dir . sprintf('%s_%s.log', $type, $date);
        
        if (!file_exists($log_file)) {
            return [];
        }
        
        $logs = [];
        $handle = fopen($log_file, 'r');
        
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $entry = json_decode(trim($line), true);
                if ($entry) {
                    if ($level && $entry['level'] !== strtoupper($level)) {
                        continue;
                    }
                    $logs[] = $entry;
                }
            }
            fclose($handle);
        }
        
        return array_slice(array_reverse($logs), 0, $limit);
    }
    
    /**
     * Get recent errors
     */
    public function getRecentErrors($limit = 50) {
        return $this->read('application', null, 'ERROR', $limit);
    }
    
    /**
     * Clear old log files
     */
    public function clearOldLogs($days = 30) {
        $files = glob($this->log_dir . '*.log');
        $cutoff = time() - ($days * 86400);
        $deleted = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Get log statistics
     */
    public function getStats($days = 7) {
        $stats = [
            'total_entries' => 0,
            'errors' => 0,
            'warnings' => 0,
            'info' => 0,
            'debug' => 0,
            'by_date' => []
        ];
        
        $files = glob($this->log_dir . 'application_*.log');
        
        foreach ($files as $file) {
            $date = date('Y-m-d', filemtime($file));
            if ($date < date('Y-m-d', strtotime("-$days days"))) {
                continue;
            }
            
            $handle = fopen($file, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $entry = json_decode(trim($line), true);
                    if ($entry) {
                        $stats['total_entries']++;
                        $level = strtolower($entry['level']);
                        if (isset($stats[$level])) {
                            $stats[$level]++;
                        }
                        $stats['by_date'][$date] = ($stats['by_date'][$date] ?? 0) + 1;
                    }
                }
                fclose($handle);
            }
        }
        
        return $stats;
    }
}
