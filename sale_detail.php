<?php
require_once 'config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT s.*, u.name as cashier_name 
    FROM sales s 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$sale = $stmt->fetch();

if (!$sale) {
    header('Location: sales.php');
    exit();
}

$items = $pdo->prepare("
    SELECT si.*, p.name as product_name, p.unit 
    FROM sale_items si 
    JOIN products p ON si.product_id = p.id 
    WHERE si.sale_id = ?
");
$items->execute([$id]);
$items = $items->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Details - SuperMarket Pro</title>
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

        .invoice-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
        }

        .btn-print {
            background: #000;
            color: #f3ca20;
            padding: 10px 25px;
            border-radius: 50px;
            border: none;
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

        @media print {

            .sidebar,
            .btn-print,
            .btn-secondary {
                display: none;
            }

            .main-content {
                margin: 0;
                padding: 0;
            }

            .invoice-card {
                box-shadow: none;
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
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="bi bi-person-circle"></i><span>Profile</span></a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="bi bi-receipt me-2"></i>Sale Details</h2>
                <div>
                    <button onclick="window.print()" class="btn-print"><i class="bi bi-printer me-2"></i>Print Invoice</button>
                    <a href="sales.php" class="btn btn-secondary">Back</a>
                </div>
            </div>

            <div class="invoice-card">
                <div class="text-center mb-4">
                    <h2 style="color: #f3ca20;">SuperMarket Pro</h2>
                    <p>123 Main Street, Kigali, Rwanda<br>Tel: +250 788 888 888 | Email: info@supermarketpro.com</p>
                    <hr>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Invoice Number:</strong> #<?php echo $sale['invoice_number']; ?><br>
                        <strong>Date:</strong> <?php echo date('F d, Y H:i:s', strtotime($sale['sale_date'])); ?><br>
                        <strong>Cashier:</strong> <?php echo $sale['cashier_name']; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <strong>Customer:</strong> <?php echo $sale['customer_name'] ?: 'Walk-in Customer'; ?><br>
                        <strong>Payment Method:</strong> <?php echo ucfirst($sale['payment_method']); ?>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></td>
                                <td>Rwf <?php echo number_format($item['unit_price'], 2); ?></td>
                                <td>Rwf <?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                            <td>Rwf <?php echo number_format($sale['subtotal'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Tax (10%):</td>
                            <td>Rwf <?php echo number_format($sale['tax'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold fs-5">Total:</td>
                            <td class="fw-bold fs-5 text-success">Rwf <?php echo number_format($sale['total_amount'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="text-center mt-4">
                    <p class="text-muted">Thank you for shopping with us!</p>
                    <p class="text-muted small">This is a computer generated invoice. No signature required.</p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>