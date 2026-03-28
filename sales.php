<?php
require_once 'config.php';
requireManager();

$message = '';
$error = '';

// Get all sales with details
$sales = $pdo->query("
    SELECT s.*, u.name as cashier_name, 
           (SELECT COUNT(*) FROM sale_items WHERE sale_id = s.id) as items_count
    FROM sales s 
    LEFT JOIN users u ON s.user_id = u.id 
    ORDER BY s.sale_date DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales History - SuperMarket Pro</title>
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
            font-family: 'Segoe UI', sans-serif;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: #000;
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
            color: #000;
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

        .btn-primary-custom {
            background: #000;
            color: #f3ca20;
            padding: 8px 15px;
            border-radius: 50px;
            text-decoration: none;
        }

        .btn-primary-custom:hover {
            background: #f3ca20;
            color: #000;
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
                <li class="nav-item"><a href="sales.php" class="nav-link active"><i class="bi bi-receipt"></i><span>Sales</span></a></li>
                <li class="nav-item"><a href="reports.php" class="nav-link"><i class="bi bi-graph-up"></i><span>Reports</span></a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="bi bi-person-circle"></i><span>Profile</span></a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1><i class="bi bi-receipt me-2"></i>Sales History</h1>
                <p>View all completed transactions</p>
            </div>

            <div class="bg-white rounded-3 p-4 border">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Subtotal</th>
                                <th>Tax</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Cashier</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo $sale['invoice_number']; ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($sale['sale_date'])); ?></td>
                                    <td><?php echo $sale['customer_name'] ?: 'Walk-in'; ?></td>
                                    <td><?php echo $sale['items_count']; ?></td>
                                    <td>Rwf <?php echo number_format($sale['subtotal'], 2); ?></td>
                                    <td>Rwf <?php echo number_format($sale['tax'], 2); ?></td>
                                    <td class="fw-bold text-success">Rwf <?php echo number_format($sale['total_amount'], 2); ?></td>
                                    <td><span class="badge bg-<?php echo $sale['payment_method'] == 'cash' ? 'success' : ($sale['payment_method'] == 'card' ? 'info' : 'warning'); ?>"><?php echo ucfirst($sale['payment_method']); ?></span></td>
                                    <td><?php echo $sale['cashier_name']; ?></td>
                                    <td><a href="sale_detail.php?id=<?php echo $sale['id']; ?>" class="btn btn-sm" style="background:#f3ca20; color:#000;"><i class="bi bi-eye"></i> View</a></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($sales)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5">No sales recorded yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>