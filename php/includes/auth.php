<?php
/**
 * Authentication and authorization helpers for the American Shipping & Logistics PHP backend.
 *
 * Usage:
 *   require_once __DIR__ . '/auth.php';
 *   require_login();
 *   $user = current_user();
 *   require_role('admin');
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns the currently authenticated user row, or null if not logged in.
 *
 * @return array<string, mixed>|null
 */
function current_user(): ?array {
    $userId = $_SESSION['user_id'] ?? ($_SESSION['admin_id'] ?? null);
    if (!$userId) {
        return null;
    }
    static $user = null;
    if ($user === null || (int)$user['id'] !== (int)$userId) {
        $user = db_fetch_one('SELECT id, name, email, role, is_active, created_at FROM users WHERE id = :id LIMIT 1', [':id' => $userId]);
        if (!$user) {
            $user = db_fetch_one('SELECT id, full_name AS name, email, role, is_active, created_at FROM manager_admin WHERE id = :id LIMIT 1', [':id' => $userId]);
        }
    }
    return $user ?: null;
}

/**
 * Ensures the current request is authenticated.
 * If not, sends a 401 JSON response for API requests or redirects to login for page requests.
 */
function require_login(): void {
    if (current_user() !== null) {
        return;
    }

    if (is_api_request()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Authentication required.', 'code' => 'UNAUTHORIZED']);
        exit;
    }

    header('Location: /deprixa/index.php');
    exit;
}

/**
 * Ensures the current user has one of the allowed roles.
 *
 * @param string|string[] $allowedRoles
 */
function require_role(string|array $allowedRoles): void {
    require_login();
    $user = current_user();
    if (!$user) {
        return;
    }

    $roles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    if (!in_array($user['role'], $roles, true)) {
        if (is_api_request()) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions.', 'code' => 'FORBIDDEN']);
            exit;
        }

        header('Location: /deprixa/index.php?error=' . urlencode('You do not have permission to access that page.'));
        exit;
    }
}

/**
 * Determines whether the current request is an API request.
 */
function is_api_request(): bool {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $path  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

    return str_contains($accept, 'application/json') || str_starts_with($path, '/api/');
}


