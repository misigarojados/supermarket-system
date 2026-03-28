<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit();
}

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - SuperMarket Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .top-nav {
            background: #000000;
            padding: 15px 0;
        }

        .logo h2 {
            color: #f3ca20;
            font-weight: 700;
            margin: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .nav-links a:hover {
            color: #f3ca20;
        }

        .product-detail {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-top: 30px;
        }

        .product-image {
            max-width: 100%;
            border-radius: 12px;
        }

        .product-price {
            font-size: 2rem;
            font-weight: 700;
            color: #f3ca20;
        }

        .btn-buy {
            background: #000;
            color: #f3ca20;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
        }

        .btn-buy:hover {
            background: #f3ca20;
            color: #000;
        }

        .stock-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
        }

        .stock-high {
            background: #d4edda;
            color: #155724;
        }

        .stock-low {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="top-nav">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo"><i class="bi bi-cart-fill"></i>
                    <h2 class="d-inline-block ms-2">SuperMarket <span>Pro</span></h2>
                </div>
                <div class="nav-links">
                    <a href="index.php"><i class="bi bi-house-fill me-1"></i>Home</a>
                    <?php if ($is_logged_in): ?>
                        <a href="dashboard.php">Dashboard</a>
                        <a href="pos.php">POS</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-5">
                <div class="product-detail">
                    <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/500x400/f5f5f5/999?text=Product+Image'; ?>" class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
            </div>
            <div class="col-md-7">
                <div class="product-detail">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="text-muted"><i class="bi bi-tag me-1"></i><?php echo $product['category_name'] ?? 'Uncategorized'; ?></p>
                    <div class="mb-3">
                        <span class="stock-badge <?php echo $product['stock_quantity'] > 10 ? 'stock-high' : 'stock-low'; ?>">
                            <i class="bi bi-<?php echo $product['stock_quantity'] > 10 ? 'check-circle' : 'exclamation-triangle'; ?> me-1"></i>
                            <?php echo $product['stock_quantity']; ?> items in stock
                        </span>
                    </div>
                    <div class="product-price">Rwf <?php echo number_format($product['price'], 2); ?></div>
                    <hr>
                    <h5>Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available.')); ?></p>
                    <div class="mt-4">
                        <?php if ($is_logged_in): ?>
                            <a href="pos.php" class="btn-buy"><i class="bi bi-cart-plus me-2"></i>Buy Now</a>
                        <?php else: ?>
                            <a href="login.php" class="btn-buy"><i class="bi bi-box-arrow-in-right me-2"></i>Login to Purchase</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>