<?php
/**
 * Database configuration — PDO (MySQLi-compatible).
 *
 * This file is a thin wrapper that:
 * 1. Registers the error handler
 * 2. Loads includes/db.php which defines all DB helper functions
 *
 * In production, set credentials via environment variables:
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET
 *
 * Shared hosting tip: set them in .htaccess:
 *   SetEnv DB_HOST localhost
 *   SetEnv DB_NAME shipping_db
 *   SetEnv DB_USER dbuser
 *   SetEnv DB_PASS dbpassword
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/error-handler.php';
register_error_handler();

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
