<?php
/**
 * Role-based access control for the Admin Dashboard.
 *
 * Normalizes the legacy session role ('admin' / 'staff') into the courier
 * role model required by the brief (Super Admin, Admin, Operations Staff,
 * Warehouse Staff, Courier, Finance, Customer Support). Promote a user by
 * setting their `role` column to one of the keys below.
 *
 * Use can($permission) to gate UI; requirePermission($permission) to hard-stop.
 */
if (!function_exists('adminRoleKey')) {
    function adminRoleKey() {
        $r = strtolower((string)($_SESSION['admin_role'] ?? 'staff'));
        // Legacy mappings.
        if ($r === 'admin') { return 'admin'; }
        if ($r === 'staff') { return 'operations'; }
        return $r;
    }
}

if (!function_exists('rolePermissions')) {
    function rolePermissions() {
        $all = ['view_shipments','edit_shipment','update_tracking','assign_courier',
            'manage_documents','delete_documents','send_notifications','delivery_confirm',
            'view_activity_log','manage_billing','manage_customs','view_analytics',
            'hold_shipment','cancel_shipment','return_shipment','archive_shipment','delete_shipment',
            'manage_integrations'];

        return [
            'super_admin' => $all,
            'admin'       => $all,
            'operations'  => ['view_shipments','edit_shipment','update_tracking','assign_courier',
                'manage_documents','send_notifications','delivery_confirm','view_activity_log',
                'manage_customs','view_analytics','hold_shipment','cancel_shipment',
                'return_shipment','archive_shipment','manage_integrations'],
            'warehouse'   => ['view_shipments','manage_documents','delivery_confirm','assign_courier'],
            'courier'     => ['view_shipments','update_tracking','delivery_confirm'],
            'finance'     => ['view_shipments','manage_billing','view_analytics'],
            'support'     => ['view_shipments','send_notifications','update_tracking','view_activity_log'],
        ];
    }
}

if (!function_exists('can')) {
    function can($permission) {
        $perms = rolePermissions();
        $role = adminRoleKey();
        return in_array($permission, $perms[$role] ?? [], true);
    }
}

if (!function_exists('requirePermission')) {
    function requirePermission($permission) {
        if (!can($permission)) {
            http_response_code(403);
            echo '<div class="alert alert-danger m-4"><i class="bi bi-shield-lock"></i> Access denied. Your role does not have permission: <code>' . htmlspecialchars($permission) . '</code>.</div>';
            require_once __DIR__ . '/footer.php';
            exit;
        }
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        return ($_SESSION['admin_role'] ?? '') === 'super_admin';
    }
}

if (!function_exists('csrfInput')) {
    function csrfInput() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'] ?? '') . '">';
    }
}

if (!function_exists('roleLabel')) {
    function roleLabel($role = null) {
        $role = $role ?? adminRoleKey();
        $map = [
            'super_admin' => 'Super Admin', 'admin' => 'Admin', 'operations' => 'Operations Staff',
            'warehouse' => 'Warehouse Staff', 'courier' => 'Courier', 'finance' => 'Finance', 'support' => 'Customer Support',
        ];
        return $map[$role] ?? ucfirst($role);
    }
}
