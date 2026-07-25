<?php
/**
 * deprixa/icon_settings.php
 *
 * Shared partial for settings pages — renders sidebar icons and nav.
 * Included by panel-customer pages.
 */

$currentPage = $currentPage ?? 'dashboard';
$iconPages = [
    'dashboard' => ['fa fa-dashboard', 'Dashboard', 'customer.php'],
    'add-shipping' => ['fa fa-cubes', 'ADD SHIPPING', 'add-courier-customer.php'],
    'pay-bill' => ['zmdi zmdi-collection-text', 'PAY BILL', 'paybill.php'],
    'profile' => ['fa fa-briefcase', 'PROFILE', 'profile_customer.php'],
    'settings' => ['zmdi zmdi-settings', 'Settings', '#'],
];
?>
<ul class="nav nav-pills nav-stacked">
    <?php foreach ($iconPages as $key => $page): ?>
        <li class="<?= $currentPage === $key ? 'active' : '' ?>">
            <a href="<?= h($page[2]) ?>">
                <i class="<?= h($page[0]) ?>"></i>
                <span><?= h($page[1]) ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
