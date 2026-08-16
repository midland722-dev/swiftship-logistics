<?php
/**
 * database-settings.php
 *
 * Compatibility shim for legacy settings endpoints.
 * Provides the conexion() function expected by actualizar.php / editar.php / etc.
 */

require_once __DIR__ . '/deprixa/database.php';

if (!function_exists('conexion')) {
    function conexion() {
        global $dbConn;
        return $dbConn;
    }
}
