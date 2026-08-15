<?php
/**
 * deprixa/database-settings.php
 *
 * Backward-compatible database connection wrapper for legacy Deprixa settings
 * scripts that expect a conexion() function returning a mysqli connection.
 */

if (!function_exists('conexion')) {
    function conexion() {
        static $con = null;
        if ($con === null) {
            $dbHost = getenv('DB_HOST') ?: 'localhost';
            $dbName = getenv('DB_NAME') ?: 'shipping_db';
            $dbUser = getenv('DB_USER') ?: 'root';
            $dbPass = getenv('DB_PASS') ?: '';
            $dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';

            $con = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
            if ($con->connect_error) {
                http_response_code(500);
                exit('Database connection failed. Please try again later.');
            }
            $con->set_charset($dbCharset);
        }
        return $con;
    }
}
