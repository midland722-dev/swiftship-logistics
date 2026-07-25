<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/permissions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$db = getDB();
$current_admin_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'Admin - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.css">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>

        <div class="sidebar-header">
            <i class="bi bi-truck"></i>
            <div>
                <h3><?php echo htmlspecialchars(SITE_NAME); ?></h3>
                <small>Admin Panel</small>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-section">Main Menu</li>
            <li>
                <a href="index.php" class="<?php echo $current_admin_page === 'index.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="../php/track.php" target="_blank">
                    <i class="bi bi-search"></i>
                    <span>Track Shipment</span>
                </a>
            </li>
            <li>
                <a href="../customer-booking.php" target="_blank">
                    <i class="bi bi-calendar-check"></i>
                    <span>Book Shipment</span>
                </a>
            </li>

            <li class="menu-section">Management</li>
            <li>
                <a href="create_shipment.php" class="<?php echo $current_admin_page === 'create_shipment.php' ? 'active' : ''; ?>">
                    <i class="bi bi-plus-circle"></i>
                    <span>Create Shipment</span>
                </a>
            </li>
            <li>
                <a href="shipments.php" class="<?php echo $current_admin_page === 'shipments.php' ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam"></i>
                    <span>Shipments</span>
                </a>
            </li>
            <li>
                <a href="settings.php" class="<?php echo $current_admin_page === 'settings.php' ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li>
                <a href="integrations.php" class="<?php echo in_array($current_admin_page, ['integrations.php','integrations_edit.php','integrations_logs.php']) ? 'active' : ''; ?>">
                    <i class="bi bi-plug-fill"></i>
                    <span>Integrations</span>
                </a>
            </li>
            <li>
                <a href="analytics.php" class="<?php echo $current_admin_page === 'analytics.php' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i>
                    <span>Analytics</span>
                </a>
            </li>
            <li>
                <a href="tracking_history.php" class="<?php echo $current_admin_page === 'tracking_history.php' ? 'active' : ''; ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Tracking History</span>
                </a>
            </li>
            <li>
                <a href="simulation.php" class="<?php echo $current_admin_page === 'simulation.php' ? 'active' : ''; ?>">
                    <i class="bi bi-play-circle"></i>
                    <span>Shipment Simulation</span>
                </a>
            </li>
            <li>
                <a href="activity_log.php" class="<?php echo $current_admin_page === 'activity_log.php' ? 'active' : ''; ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>Activity Log</span>
                </a>
            </li>
            <li>
                <a href="users.php" class="<?php echo $current_admin_page === 'users.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="tickets.php" class="<?php echo $current_admin_page === 'tickets.php' ? 'active' : ''; ?>">
                    <i class="bi bi-ticket-perforated"></i>
                    <span>Support Tickets</span>
                </a>
            </li>
            <li>
                <a href="customers.php" class="<?php echo in_array($current_admin_page, ['customers.php','customer_details.php','customer-shipments.php','customer-booking.php','customer-payments.php','customer-profile.php']) ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li>
                <a href="panel-customer.php" class="<?php echo $current_admin_page === 'panel-customer.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-badge"></i>
                    <span>Customer Panel</span>
                </a>
            </li>

            <li class="menu-section">Website</li>
            <li>
                <a href="../php/index.php" target="_blank">
                    <i class="bi bi-globe"></i>
                    <span>View Website</span>
                </a>
            </li>
            <li>
                <a href="../php/contact.php" target="_blank">
                    <i class="bi bi-envelope"></i>
                    <span>Contact Page</span>
                </a>
            </li>

            <li class="menu-section">Account</li>
            <li>
                <a href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-header">
            <div>
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
                <?php if (isset($_SESSION['admin_name'])): ?>
                    <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle-sm" id="themeToggle" aria-label="Toggle theme" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 0.5rem 0.75rem; cursor: pointer; color: var(--text-primary); font-size: 1rem;">
                    <i class="bi bi-moon-stars"></i>
                </button>
            </div>
        </div>

        <script>
            // Sidebar toggle
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const mainContent = document.getElementById('mainContent');
            const themeToggle = document.getElementById('themeToggle');

            function toggleSidebar() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('mobile-open');
                    mobileOverlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
                } else {
                    sidebar.classList.toggle('collapsed');
                    const icon = sidebarToggle.querySelector('i');
                    if (sidebar.classList.contains('collapsed')) {
                        icon.className = 'bi bi-chevron-right';
                    } else {
                        icon.className = 'bi bi-chevron-left';
                    }
                }
            }

            sidebarToggle.addEventListener('click', toggleSidebar);
            mobileMenuBtn.addEventListener('click', toggleSidebar);
            mobileOverlay.addEventListener('click', toggleSidebar);

            // Theme toggle
            const currentTheme = localStorage.getItem('adminTheme') || 'light';
            document.documentElement.setAttribute('data-theme', currentTheme);
            updateThemeIcon(currentTheme);

            themeToggle.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('adminTheme', next);
                updateThemeIcon(next);
            });

            function updateThemeIcon(theme) {
                const icon = themeToggle.querySelector('i');
                icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
            }

            // Close mobile menu on link click
            sidebar.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('mobile-open');
                        mobileOverlay.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });

            // Close mobile menu on escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Handle resize
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        </script>
