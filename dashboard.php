<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

// Get statistics
if ($role == 'admin') {
    $stats = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch();
    $products = $pdo->query("SELECT COUNT(*) as total FROM products")->fetch();
    $sales = $pdo->query("SELECT COUNT(*) as total, COALESCE(SUM(total_amount),0) as revenue FROM sales WHERE payment_status='paid'")->fetch();
    $low_stock = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity <= min_stock_level AND status='active'")->fetch();
} else {
    $products = $pdo->query("SELECT COUNT(*) as total FROM products WHERE status='active'")->fetch();
    $today_sales = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total_amount),0) as revenue FROM sales WHERE DATE(sale_date)=CURDATE() AND payment_status='paid'");
    $today_sales->execute();
    $sales = $today_sales->fetch();
    $low_stock = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity <= min_stock_level AND status='active'")->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SuperMarket Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
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

        .page-header {
            background: white;
            border-radius: 16px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #f3ca20;
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #000;
            margin-bottom: 5px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #f3ca20;
            margin: 10px 0;
        }

        .stat-icon {
            font-size: 2.5rem;
            color: #000;
        }

        .quick-actions {
            background: white;
            border-radius: 16px;
            padding: 25px;
            border: 1px solid #e0e0e0;
            margin-bottom: 30px;
        }

        .btn-primary-custom {
            background: #000;
            color: #f3ca20;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-primary-custom:hover {
            background: #f3ca20;
            color: #000;
            transform: translateY(-2px);
        }

        .btn-secondary-custom {
            background: #f3ca20;
            color: #000;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-secondary-custom:hover {
            background: #e0b800;
            transform: translateY(-2px);
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
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
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
</head>

<body>
    <div class="app-wrapper">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <h3><i class="bi bi-cart-fill"></i> SuperMarket</h3>
                <p><?php echo ucfirst($role); ?> Panel</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a href="pos.php" class="nav-link"><i class="bi bi-cart-check"></i><span>Point of Sale</span></a></li>
                <li class="nav-item"><a href="products.php" class="nav-link"><i class="bi bi-box-seam"></i><span>Products</span></a></li>
                <?php if ($role == 'admin' || $role == 'manager'): ?>
                    <li class="nav-item"><a href="inventory.php" class="nav-link"><i class="bi bi-clipboard-data"></i><span>Inventory</span></a></li>
                    <li class="nav-item"><a href="sales.php" class="nav-link"><i class="bi bi-receipt"></i><span>Sales</span></a></li>
                    <li class="nav-item"><a href="reports.php" class="nav-link"><i class="bi bi-graph-up"></i><span>Reports</span></a></li>
                <?php endif; ?>
                <?php if ($role == 'admin'): ?>
                    <li class="nav-item"><a href="users.php" class="nav-link"><i class="bi bi-people-fill"></i><span>Users</span></a></li>
                    <li class="nav-item"><a href="categories.php" class="nav-link"><i class="bi bi-tags"></i><span>Categories</span></a></li>
                    <li class="nav-item"><a href="suppliers.php" class="nav-link"><i class="bi bi-truck"></i><span>Suppliers</span></a></li>
                <?php endif; ?>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="bi bi-person-circle"></i><span>Profile</span></a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="bi bi-chevron-left" id="toggleIcon"></i>
        </button>

        <div class="main-content" id="mainContent">
            <div class="page-header">
                <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>

            <div class="row g-4 mb-4">
                <?php if ($role == 'admin'): ?>
                    <div class="col-md-3">
                        <div class="stat-card text-center">
                            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                            <h3><?php echo $stats['total']; ?></h3>
                            <p class="text-muted">Total Users</p>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                        <h3><?php echo $products['total']; ?></h3>
                        <p class="text-muted">Total Products</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="bi bi-cart-check"></i></div>
                        <h3><?php echo $sales['total']; ?></h3>
                        <p class="text-muted">Total Sales</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                        <h3>Rwf <?php echo number_format($sales['revenue'], 2); ?></h3>
                        <p class="text-muted">Total Revenue</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill" style="color: #dc3545;"></i></div>
                        <h3><?php echo $low_stock['total']; ?></h3>
                        <p class="text-muted">Low Stock Items</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h5 class="mb-3"><i class="bi bi-lightning-charge-fill me-2"></i>Quick Actions</h5>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="pos.php" class="btn-primary-custom"><i class="bi bi-cart-check"></i> New Sale</a>
                    <a href="products.php?action=add" class="btn-primary-custom"><i class="bi bi-plus-circle"></i> Add Product</a>
                    <a href="reports.php" class="btn-secondary-custom"><i class="bi bi-graph-up"></i> View Reports</a>
                </div>
            </div>

            <div class="bg-white rounded-3 p-4 border">
                <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Recent Sales</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Cashier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $pdo->query("SELECT s.*, u.name as cashier FROM sales s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.sale_date DESC LIMIT 5");
                            while ($sale = $recent->fetch()): ?>
                                <tr>
                                    <td>#<?php echo $sale['invoice_number']; ?></td>
                                    <td><?php echo $sale['customer_name'] ?: 'Walk-in'; ?></td>
                                    <td>Rwf<?php echo number_format($sale['total_amount'], 2); ?></td>
                                    <td><?php echo date('M d, H:i', strtotime($sale['sale_date'])); ?></td>
                                    <td><?php echo $sale['cashier']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleIcon = document.getElementById('toggleIcon');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');

            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.remove('bi-chevron-left');
                toggleIcon.classList.add('bi-chevron-right');
            } else {
                toggleIcon.classList.remove('bi-chevron-right');
                toggleIcon.classList.add('bi-chevron-left');
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>