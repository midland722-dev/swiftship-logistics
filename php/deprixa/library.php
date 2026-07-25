<?php
/**
 * deprixa/library.php
 *
 * Legacy helper functions for the Deprixa v2.5 admin/customer panel.
 */

// Prevent direct access
if (!defined('DEPRIXA_LIBRARY')) {
    define('DEPRIXA_LIBRARY', true);
}

/**
 * Checks whether the current session is an authenticated user.
 * If not authenticated, redirects to the login page.
 */
function isUser() {
    if (!isset($_SESSION['user_name']) || empty($_SESSION['user_name'])) {
        header('Location: ../index.php');
        exit;
    }
}

/**
 * Executes a SQL query using the legacy mysql_* layer.
 * Returns the mysqli result resource on success, or false on failure.
 */
function dbQuery($sql) {
    global $dbConn;
    $result = mysql_query($sql, $dbConn);
    if ($result === false) {
        error_log('dbQuery error: ' . mysql_error($dbConn) . ' | SQL: ' . $sql);
    }
    return $result;
}

/**
 * Returns the current logged-in user's name.
 */
function getUserName() {
    return $_SESSION['user_name'] ?? '';
}

/**
 * Returns the current logged-in user's ID.
 */
function getUserId() {
    return $_SESSION['user_id'] ?? 0;
}

/**
 * Returns the current logged-in user's role / office name.
 */
function getUserOffice() {
    return $_SESSION['user_type'] ?? '';
}
