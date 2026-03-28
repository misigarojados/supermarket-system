<?php
// sidebar.php - Include this in all pages
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['user_role'] ?? 'guest';
?>

<style>
    /* Sidebar Styles - Copy these to your main CSS file */
    .sidebar {
        width: 280px;
        background: #000000;
        position: fixed;
        height: 100vh;
        left: 0;
        top: 0;
        overflow-y: auto;
        transition: all 0.3s ease;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .sidebar.collapsed {
        width: 80px;
    }

    .sidebar.collapsed .sidebar-brand h3,
    .sidebar.collapsed .sidebar-brand p,
    .sidebar.collapsed .nav-link span {
        display: none;
    }

    .sidebar.collapsed .nav-link {
        justify-content: center;
        padding: 12px;
    }

    .sidebar.collapsed .nav-link i {
        margin: 0;
    }

    .sidebar-brand {
        padding: 25px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    .sidebar-brand h3 {
        color: #f3ca20;
        font-weight: 700;
        margin: 0;
        font-size: 1.5rem;
    }

    .sidebar-brand p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.8rem;
        margin-top: 5px;
    }

    .nav-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-item {
        margin: 5px 15px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 18px;
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        border-radius: 12px;
        transition: all 0.3s;
        font-weight: 500;
        gap: 12px;
    }

    .nav-link i {
        font-size: 1.2rem;
        width: 24px;
    }

    .nav-link:hover {
        background: rgba(243, 202, 32, 0.2);
        color: #f3ca20;
    }

    .nav-link.active {
        background: #f3ca20;
        color: #000000;
    }

    /* Sidebar Toggle Button */
    .sidebar-toggle {
        position: fixed;
        left: 290px;
        top: 20px;
        background: #f3ca20;
        color: #000;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        z-index: 1001;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .sidebar-toggle:hover {
        transform: scale(1.05);
        background: #e0b800;
    }

    .sidebar.collapsed+.sidebar-toggle {
        left: 90px;
    }

    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 30px 40px;
        transition: all 0.3s;
    }

    .main-content.expanded {
        margin-left: 80px;
    }

    @media (max-width: 992px) {
        .sidebar {
            width: 80px;
        }

        .sidebar-brand h3,
        .sidebar-brand p,
        .nav-link span {
            display: none;
        }

        .nav-link {
            justify-content: center;
            padding: 12px;
        }

        .main-content {
            margin-left: 80px;
        }

        .sidebar-toggle {
            left: 90px;
        }

        .sidebar.collapsed {
            width: 0;
            overflow: hidden;
        }

        .sidebar.collapsed+.sidebar-toggle {
            left: 10px;
        }

        .main-content.expanded {
            margin-left: 0;
        }
    }

    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f5f5f5;
    }

    ::-webkit-scrollbar-thumb {
        background: #000;
        border-radius: 4px;
    }
</style>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h3><i class="bi bi-cart-fill"></i> SuperMarket</h3>
        <p><?php echo ucfirst($role); ?> Panel</p>
    </div>
    <ul class="nav-menu">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
            </a>
        </li>

        <!-- POS - All logged in users -->
        <li class="nav-item">
            <a href="pos.php" class="nav-link <?php echo $current_page == 'pos.php' ? 'active' : ''; ?>">
                <i class="bi bi-cart-check"></i><span>Point of Sale</span>
            </a>
        </li>

        <!-- Products - All logged in users -->
        <li class="nav-item">
            <a href="products.php" class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i><span>Products</span>
            </a>
        </li>

        <!-- Manager/Admin Only -->
        <?php if ($role == 'admin' || $role == 'manager'): ?>
            <li class="nav-item">
                <a href="inventory.php" class="nav-link <?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>">
                    <i class="bi bi-clipboard-data"></i><span>Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="sales.php" class="nav-link <?php echo $current_page == 'sales.php' ? 'active' : ''; ?>">
                    <i class="bi bi-receipt"></i><span>Sales</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i><span>Reports</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Admin Only -->
        <?php if ($role == 'admin'): ?>
            <li class="nav-item">
                <a href="users.php" class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i><span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                    <i class="bi bi-tags"></i><span>Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="suppliers.php" class="nav-link <?php echo $current_page == 'suppliers.php' ? 'active' : ''; ?>">
                    <i class="bi bi-truck"></i><span>Suppliers</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Profile - All logged in users -->
        <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i><span>Profile</span>
            </a>
        </li>

        <!-- Logout -->
        <li class="nav-item">
            <a href="logout.php" class="nav-link">
                <i class="bi bi-box-arrow-right"></i><span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
    <i class="bi bi-chevron-left" id="toggleIcon"></i>
</button>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        const toggleIcon = document.getElementById('toggleIcon');

        sidebar.classList.toggle('collapsed');
        if (mainContent) mainContent.classList.toggle('expanded');

        // Save state to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);

        if (toggleIcon) {
            if (isCollapsed) {
                toggleIcon.classList.remove('bi-chevron-left');
                toggleIcon.classList.add('bi-chevron-right');
            } else {
                toggleIcon.classList.remove('bi-chevron-right');
                toggleIcon.classList.add('bi-chevron-left');
            }
        }
    }

    // Load saved sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleIcon = document.getElementById('toggleIcon');

            if (sidebar) sidebar.classList.add('collapsed');
            if (mainContent) mainContent.classList.add('expanded');
            if (toggleIcon) {
                toggleIcon.classList.remove('bi-chevron-left');
                toggleIcon.classList.add('bi-chevron-right');
            }
        }
    });
</script>