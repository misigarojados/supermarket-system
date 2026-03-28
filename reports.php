<?php
require_once 'config.php';
requireManager();

// Get date filters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'daily';

// Sales summary
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_sales,
        COALESCE(SUM(total_amount),0) as total_revenue,
        COALESCE(AVG(total_amount),0) as avg_sale,
        COUNT(DISTINCT user_id) as cashiers_count
    FROM sales 
    WHERE DATE(sale_date) BETWEEN ? AND ? 
    AND payment_status = 'paid'
");
$stmt->execute([$start_date, $end_date]);
$summary = $stmt->fetch();

// Top selling products
$top_products = $pdo->prepare("
    SELECT p.name, SUM(si.quantity) as total_sold, SUM(si.subtotal) as total_revenue
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE DATE(s.sale_date) BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10
");
$top_products->execute([$start_date, $end_date]);
$top_products = $top_products->fetchAll();

// Daily sales for chart
$daily_sales = $pdo->prepare("
    SELECT DATE(sale_date) as date, COUNT(*) as count, COALESCE(SUM(total_amount),0) as revenue
    FROM sales
    WHERE DATE(sale_date) BETWEEN ? AND ?
    AND payment_status = 'paid'
    GROUP BY DATE(sale_date)
    ORDER BY date
");
$daily_sales->execute([$start_date, $end_date]);
$daily_sales = $daily_sales->fetchAll();

// Sales by payment method
$payment_methods = $pdo->prepare("
    SELECT payment_method, COUNT(*) as count, COALESCE(SUM(total_amount),0) as revenue
    FROM sales
    WHERE DATE(sale_date) BETWEEN ? AND ?
    AND payment_status = 'paid'
    GROUP BY payment_method
");
$payment_methods->execute([$start_date, $end_date]);
$payment_methods = $payment_methods->fetchAll();

// Low stock products
$low_stock = $pdo->query("SELECT name, stock_quantity, min_stock_level FROM products WHERE stock_quantity <= min_stock_level AND status='active' ORDER BY stock_quantity ASC LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - SuperMarket Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: #000000;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand h3 {
            color: #f3ca20;
            font-weight: 700;
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
            gap: 12px;
        }

        .nav-link:hover {
            background: rgba(243, 202, 32, 0.2);
            color: #f3ca20;
        }

        .nav-link.active {
            background: #f3ca20;
            color: #000000;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px 40px;
        }

        .page-header {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #f3ca20;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #f3ca20;
        }

        .report-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
        }

        .chart-container {
            height: 300px;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }

            .sidebar-brand h3,
            .nav-link span {
                display: none;
            }

            .nav-link {
                justify-content: center;
            }

            .main-content {
                margin-left: 80px;
            }
        }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <div class="sidebar">
            <div class="sidebar-brand">
                <h3><i class="bi bi-cart-fill"></i></h3>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a href="pos.php" class="nav-link"><i class="bi bi-cart-check"></i><span>POS</span></a></li>
                <li class="nav-item"><a href="products.php" class="nav-link"><i class="bi bi-box-seam"></i><span>Products</span></a></li>
                <li class="nav-item"><a href="reports.php" class="nav-link active"><i class="bi bi-graph-up"></i><span>Reports</span></a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="bi bi-person-circle"></i><span>Profile</span></a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1><i class="bi bi-graph-up me-2"></i>Sales Reports</h1>
                <p>View sales analytics and inventory reports</p>
            </div>

            <!-- Date Filter -->
            <div class="report-card">
                <form method="GET" class="row g-3">
                    <div class="col-md-4"><label>Start Date</label><input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>"></div>
                    <div class="col-md-4"><label>End Date</label><input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>"></div>
                    <div class="col-md-4"><label>&nbsp;</label><button type="submit" class="btn w-100" style="background:#000; color:#f3ca20;">Generate Report</button></div>
                </form>
            </div>

            <!-- Summary Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card"><i class="bi bi-receipt" style="font-size: 2rem;"></i>
                        <h3><?php echo $summary['total_sales']; ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card"><i class="bi bi-cash-stack" style="font-size: 2rem;"></i>
                        <h3>Rwf <?php echo number_format($summary['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card"><i class="bi bi-calculator" style="font-size: 2rem;"></i>
                        <h3>Rwf <?php echo number_format($summary['avg_sale'], 2); ?></h3>
                        <p>Average Sale</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card"><i class="bi bi-people" style="font-size: 2rem;"></i>
                        <h3><?php echo $summary['cashiers_count']; ?></h3>
                        <p>Active Cashiers</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="report-card">
                        <h5><i class="bi bi-graph-up me-2"></i>Daily Sales Trend</h5>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="report-card">
                        <h5><i class="bi bi-pie-chart me-2"></i>Payment Methods</h5>
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="paymentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="report-card">
                        <h5><i class="bi bi-trophy me-2"></i>Top Selling Products</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td><?php echo $p['total_sold']; ?></td>
                                        <td>Rwf <?php echo number_format($p['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="report-card">
                        <h5><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alert</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Current Stock</th>
                                    <th>Min Level</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock as $p): ?>
                                    <tr class="text-danger">
                                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td><?php echo $p['stock_quantity']; ?></td>
                                        <td><?php echo $p['min_stock_level']; ?></td>
                                        <td><span class="badge bg-danger">Reorder</span></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($low_stock)): ?><tr>
                                        <td colspan="4" class="text-center">All products have sufficient stock</td>
                                    </tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Daily Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const dates = <?php echo json_encode(array_column($daily_sales, 'date')); ?>;
        const revenues = <?php echo json_encode(array_column($daily_sales, 'revenue')); ?>;

        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Revenue (RWF)',
                    data: revenues,
                    borderColor: '#f3ca20',
                    backgroundColor: 'rgba(243,202,32,0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Payment Methods Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const methods = <?php echo json_encode(array_column($payment_methods, 'payment_method')); ?>;
        const amounts = <?php echo json_encode(array_column($payment_methods, 'revenue')); ?>;

        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: methods.map(m => m.charAt(0).toUpperCase() + m.slice(1)),
                datasets: [{
                    data: amounts,
                    backgroundColor: ['#f3ca20', '#000000', '#666666']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>