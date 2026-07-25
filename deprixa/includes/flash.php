<?php
/** Render and clear a one-time flash message set via $_SESSION['flash']. */
if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    $ft = $f['type'] ?? 'info';
    $fm = $f['msg'] ?? '';
    echo '<div class="alert alert-' . htmlspecialchars($ft) . ' alert-dismissible fade show m-3" role="alert">'
        . htmlspecialchars($fm)
        . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['flash']);
}
