<?php
/**
 * deprixa/database.php
 *
 * Legacy database bootstrap for the Deprixa v2.5 admin/customer panel.
 * Provides a mysqli connection and mysql_* polyfills so the legacy
 * codebase can run on modern PHP (7.4+ / 8.x).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------------------
// Connection settings — align with php/config/db.php defaults.
// ---------------------------------------------------------------------------
$dbHost = getenv('DB_HOST') ?: '';
$dbName = getenv('DB_NAME') ?: '';
$dbUser = getenv('DB_USER') ?: '';
$dbPass = getenv('DB_PASS') ?: '';
$dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';

$dbConn = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$dbConn) {
    // In production replace with a user-friendly error page
    http_response_code(500);
    exit('Database connection failed. Please try again later.');
}
mysqli_set_charset($dbConn, $dbCharset);

// ---------------------------------------------------------------------------
// mysql_* polyfills (the legacy extension was removed in PHP 7).
// ---------------------------------------------------------------------------
if (!defined('MYSQL_BOTH'))   define('MYSQL_BOTH',   3);
if (!defined('MYSQL_ASSOC'))  define('MYSQL_ASSOC',  1);
if (!defined('MYSQL_NUM'))    define('MYSQL_NUM',    2);

if (!function_exists('mysql_connect')) {
    function mysql_connect($host = null, $user = null, $pass = null) {
        global $dbConn;
        return $dbConn ?: null;
    }
}

if (!function_exists('mysql_select_db')) {
    function mysql_select_db($dbname, $link = null) {
        global $dbConn;
        $link = $link ?: $dbConn;
        return mysqli_select_db($link, $dbname);
    }
}

if (!function_exists('mysql_query')) {
    function mysql_query($query, $link = null) {
        global $dbConn;
        $link = $link ?: $dbConn;
        $result = mysqli_query($link, $query);
        if ($result === false) {
            trigger_error('MySQL query error: ' . mysqli_error($link) . ' | Query: ' . $query, E_USER_WARNING);
        }
        return $result;
    }
}

if (!function_exists('mysql_fetch_array')) {
    function mysql_fetch_array($result, $result_type = MYSQL_BOTH) {
        if ($result_type === MYSQL_ASSOC) {
            return mysqli_fetch_assoc($result);
        }
        if ($result_type === MYSQL_NUM) {
            return mysqli_fetch_row($result);
        }
        // MYSQL_BOTH
        $row = mysqli_fetch_assoc($result);
        if ($row === null) {
            return null;
        }
        return array_merge($row, array_values($row));
    }
}

if (!function_exists('mysql_fetch_assoc')) {
    function mysql_fetch_assoc($result) {
        return mysqli_fetch_assoc($result);
    }
}

if (!function_exists('mysql_fetch_row')) {
    function mysql_fetch_row($result) {
        return mysqli_fetch_row($result);
    }
}

if (!function_exists('mysql_num_rows')) {
    function mysql_num_rows($result) {
        return mysqli_num_rows($result);
    }
}

if (!function_exists('mysql_affected_rows')) {
    function mysql_affected_rows($link = null) {
        global $dbConn;
        $link = $link ?: $dbConn;
        return mysqli_affected_rows($link);
    }
}

if (!function_exists('mysql_insert_id')) {
    function mysql_insert_id($link = null) {
        global $dbConn;
        $link = $link ?: $dbConn;
        return mysqli_insert_id($link);
    }
}

if (!function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string($string, $link = null) {
        global $dbConn;
        $link = $link ?: $dbConn;
        return mysqli_real_escape_string($link, (string)$string);
    }
}

if (!function_exists('mysql_error')) {
    function mysql_error($link = null) {
        global $dbConn;
        $link = $link ?: $dbConn;
        return mysqli_error($link);
    }
}

if (!function_exists('mysql_close')) {
    function mysql_close($link = null) {
        global $dbConn;
        $link = $link ?: $dbConn;
        return mysqli_close($link);
    }
}

if (!function_exists('mysql_free_result')) {
    function mysql_free_result($result) {
        return mysqli_free_result($result);
    }
}

if (!function_exists('mysql_data_seek')) {
    function mysql_data_seek($result, $row_number) {
        return mysqli_data_seek($result, (int)$row_number);
    }
}

if (!function_exists('mysql_list_tables')) {
    function mysql_list_tables($database, $link = null) {
        global $dbConn;
        $link = $link ?: $dbConn;
        $result = mysqli_query($link, "SHOW TABLES FROM `" . mysqli_real_escape_string($link, $database) . "`");
        return $result ?: false;
    }
}
