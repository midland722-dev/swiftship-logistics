<?php
/**
 * deprixa/funciones.php
 *
 * Shared utility functions for the Deprixa v2.5 panel.
 */

if (!defined('DEPRIXA_FUNCIONES')) {
    define('DEPRIXA_FUNCIONES', true);
}

/**
 * Returns the provided value if it is set and non-empty, otherwise the default.
 *
 * @param mixed $value   Typically $_GET[$key] or $_POST[$key]
 * @param mixed $default Fallback when value is missing or empty
 * @return mixed
 */
function getParam($value, $default = '') {
    return isset($value) && $value !== '' ? $value : $default;
}

/**
 * Sanitises a value for direct insertion into a SQL statement.
 *
 * Supported types:
 *   - "int"  : cast to integer
 *   - "str"  : wrap in single quotes and escape using mysql_real_escape_string
 *   - default: treat as string
 *
 * @param mixed  $value
 * @param string $type "int" | "str" | null
 * @return string
 */
function sqlValue($value, string $type = 'str'): string {
    if ($type === 'int') {
        return (int)$value;
    }
    return "'" . mysql_real_escape_string((string)$value) . "'";
}

/**
 * Queries a single field value from a table.
 *
 * SECURITY: Uses mysql_real_escape_string on all dynamic values.
 * Table/column names are NOT user-supplied in the legacy codebase;
 * if they ever become user-supplied, add an allowlist before calling.
 *
 * @param string $campo  Column name to return
 * @param string $tabla  Table name
 * @param string $where  WHERE clause (without the WHERE keyword)
 * @return string
 */
function consultar($campo, $tabla, $where) {
    $campo = mysql_real_escape_string($campo);
    $tabla = mysql_real_escape_string($tabla);
    $where = mysql_real_escape_string($where);
    $sql = mysql_query("SELECT `$campo` FROM `$tabla` WHERE $where");
    if ($row = mysql_fetch_array($sql)) {
        return $row[$campo];
    }
    return '';
}

/**
 * Returns the SUM of a numeric column for a given condition.
 *
 * SECURITY: Uses mysql_real_escape_string on all dynamic values.
 *
 * @param string $cuenta Column to sum
 * @param string $tabla  Table name
 * @param string $where  WHERE clause (without the WHERE keyword)
 * @return float
 */
function abonos_saldo($tabla, $cuenta, $where) {
    $tabla = mysql_real_escape_string($tabla);
    $cuenta = mysql_real_escape_string($cuenta);
    $where = mysql_real_escape_string($where);
    $sql = mysql_query("SELECT SUM(`$cuenta`) AS valores FROM `$tabla` WHERE $where");
    if ($row = mysql_fetch_array($sql)) {
        return (float)($row['valores'] ?? 0);
    }
    return 0.0;
}

/**
 * Simple XOR-style encryption (legacy Deprixa compatibility).
 */
function encrypt($string, $key) {
    $result = '';
    $key = $key . '2013';
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result .= $char;
    }
    return base64_encode($result);
}

/**
 * Decrypts a string encrypted with encrypt().
 */
function decrypt($string, $key) {
    $result = '';
    $key = $key . '2013';
    $string = base64_decode($string);
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) - ord($keychar));
        $result .= $char;
    }
    return $result;
}

/**
 * Returns the weekday name in Spanish for a given date.
 */
function diaSemana($ano, $mes, $dia) {
    $dias = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
    $idx = (int)date('w', mktime(0, 0, 0, (int)$mes, (int)$dia, (int)$ano));
    return $dias[$idx] ?? '';
}

/**
 * Returns a fixed dummy string used by legacy templates.
 */
function cadenas() {
    return 'YABCDFGJAH';
}

/**
 * Formats a MySQL date (YYYY-MM-DD) to DD / MMM / YYYY.
 */
function fecha($fecha) {
    $meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
    $a = substr($fecha, 0, 4);
    $m = substr($fecha, 5, 2);
    $d = substr($fecha, 8);
    return $d . ' / ' . ($meses[(int)$m - 1] ?? '') . ' / ' . $a;
}

/**
 * Renders a legacy status badge.
 */
function estado($estado) {
    if ($estado === 's') {
        return '<span class="label label-success">Activo</span>';
    } elseif ($estado === 'n') {
        return '<span class="label label-important">No Activo</span>';
    }
    return '';
}

/**
 * Renders a legacy alert box.
 */
function mensajes($mensaje, $tipo) {
    if ($tipo === 'verde') {
        $tipo = 'alert alert-success';
    } elseif ($tipo === 'rojo') {
        $tipo = 'alert alert-error';
    } elseif ($tipo === 'azul') {
        $tipo = 'alert alert-info';
    }
    return '<div class="' . $tipo . '" align="center">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <strong>' . $mensaje . '</strong>
            </div>';
}

/**
 * Formats a number with 2 decimals using comma as decimal separator.
 */
function formato($valor) {
    return number_format((float)$valor, 2, ',', '.');
}

/**
 * Mimics mysql_real_escape_string behaviour without relying on the removed
 * mysql extension. Safe for use inside string literals.
 */
function mysql_escape_mimic($inp) {
    if (is_array($inp)) {
        return array_map('mysql_escape_mimic', $inp);
    }
    if (!empty($inp) && is_string($inp)) {
        return str_replace(
            ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
            $inp
        );
    }
    return $inp;
}

/**
 * Strips tags, slashes, and escapes a string for safe output.
 */
function limpiar($tags) {
    $tags = strip_tags($tags);
    $tags = stripslashes($tags);
    $tags = mysql_escape_mimic($tags);
    $tags = trim($tags);
    return $tags;
}
