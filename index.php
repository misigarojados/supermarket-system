<?php
require_once 'config.php';

// Get filter parameters
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;

// Build query for active products
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' AND p.stock_quantity > 0";
$params = [];

if ($category > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.barcode LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " AND p.price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;

$query .= " ORDER BY p.created_at DESC LIMIT 12";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT id, name FROM categories WHERE status='active' ORDER BY name")->fetchAll();

// Get featured products
$featured = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status='active' AND p.stock_quantity > 0 ORDER BY p.created_at DESC LIMIT 4")->fetchAll();

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>SuperMarket Pro - Shop Fresh Products</title>
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

        /* Top Navigation - Mobile First */
        .top-nav {
            background: #000000;
            padding: 10px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo h2 {
            color: #f3ca20;
            font-weight: 700;
            margin: 0;
            font-size: 1.2rem;
        }

        .logo i {
            color: #f3ca20;
            font-size: 1.5rem;
        }

        .nav-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.85rem;
            padding: 5px 8px;
            white-space: nowrap;
        }

        .nav-links a:hover {
            color: #f3ca20;
        }

        .btn-cart {
            background: #f3ca20;
            color: #000000 !important;
            border-radius: 50px;
            padding: 6px 12px !important;
            font-weight: 600;
        }

        .btn-cart:hover {
            background: #e0b800;
            color: #000 !important;
        }

        /* Hero Section - Mobile Responsive */
        .hero {
            background: linear-gradient(135deg, #000000 0%, #333333 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .hero h1 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .hero h1 span {
            color: #f3ca20;
        }

        .hero p {
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .search-box {
            max-width: 100%;
            margin: 20px auto 0;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 50px;
            font-size: 0.9rem;
            min-width: 150px;
        }

        .search-box button {
            background: #f3ca20;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        /* Product Cards - Mobile Optimized */
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            height: 180px;
            background-size: cover;
            background-position: center;
            position: relative;
            background-color: #f5f5f5;
        }

        .product-price {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #f3ca20;
            color: #000;
            padding: 4px 10px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .product-body {
            padding: 12px;
        }

        .product-title {
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 6px;
            line-height: 1.3;
        }

        .product-title a {
            color: #333;
            text-decoration: none;
        }

        .product-title a:hover {
            color: #f3ca20;
        }

        .product-category {
            font-size: 0.7rem;
            color: #999;
            margin-bottom: 6px;
        }

        .btn-add-cart {
            background: #000;
            color: #f3ca20;
            border: none;
            padding: 6px;
            border-radius: 50px;
            width: 100%;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.3s;
        }

        .btn-add-cart:hover {
            background: #f3ca20;
            color: #000;
        }

        /* Sidebar Filters - Mobile First */
        .filter-sidebar {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            position: sticky;
            top: 70px;
        }

        .filter-sidebar h5 {
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .filter-section {
            margin-bottom: 20px;
        }

        .filter-section h6 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 0.9rem;
            color: #000;
        }

        .price-range {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .price-range input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.85rem;
            min-width: 80px;
        }

        .price-range input::placeholder {
            font-size: 0.75rem;
        }

        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .category-list li {
            margin: 0;
        }

        .category-list a {
            color: #666;
            text-decoration: none;
            display: inline-block;
            padding: 5px 12px;
            background: #f5f5f5;
            border-radius: 20px;
            font-size: 0.8rem;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .category-list a:hover,
        .category-list a.active {
            color: #000;
            background: #f3ca20;
            font-weight: 600;
        }

        /* Mobile Filter Toggle Button */
        .filter-toggle {
            display: none;
            background: #000;
            color: #f3ca20;
            border: none;
            padding: 10px;
            border-radius: 8px;
            width: 100%;
            margin-bottom: 15px;
            font-weight: 600;
        }

        /* Container Padding */
        .container {
            padding-left: 15px;
            padding-right: 15px;
        }

        .py-5 {
            padding-top: 2rem !important;
            padding-bottom: 2rem !important;
        }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            .logo h2 {
                font-size: 1rem;
            }

            .logo i {
                font-size: 1.2rem;
            }

            .nav-links a {
                font-size: 0.75rem;
                padding: 4px 6px;
            }

            .hero h1 {
                font-size: 1.5rem;
            }

            .hero p {
                font-size: 0.85rem;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box button {
                width: 100%;
            }

            .product-image {
                height: 160px;
            }

            .filter-toggle {
                display: block;
            }

            .filter-sidebar {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1050;
                background: white;
                margin: 0;
                border-radius: 0;
                overflow-y: auto;
                padding: 20px;
            }

            .filter-sidebar.show {
                display: block;
            }

            .filter-sidebar .close-filter {
                display: block;
                text-align: right;
                font-size: 1.5rem;
                cursor: pointer;
                margin-bottom: 15px;
            }

            .category-list {
                flex-wrap: wrap;
            }

            .price-range input {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .top-nav .container {
                flex-direction: column;
                gap: 10px;
            }

            .logo {
                justify-content: center;
            }

            .nav-links {
                justify-content: center;
            }

            .hero h1 {
                font-size: 1.3rem;
            }

            .product-title {
                font-size: 0.85rem;
            }

            .product-category {
                font-size: 0.65rem;
            }

            .btn-add-cart {
                font-size: 0.75rem;
                padding: 5px;
            }
        }

        /* Close button for mobile filter */
        .close-filter {
            display: none;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 1060;
        }

        @media (max-width: 768px) {
            .close-filter {
                display: block;
            }
        }

        /* Overlay for mobile filter */
        .filter-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }

        .filter-overlay.show {
            display: block;
        }

        /* Product Grid Responsive */
        @media (max-width: 768px) {
            .row>[class*="col-"] {
                padding-left: 8px;
                padding-right: 8px;
            }

            .row {
                margin-left: -8px;
                margin-right: -8px;
            }
        }
    </style>
</head>

<body>
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="logo">
                    <i class="bi bi-cart-fill"></i>
                    <h2 class="d-inline-block ms-2">Neza-Supermarket <span>Tech</span></h2>
                </div>
                <div class="nav-links">
                    <a href="index.php"><i class="bi bi-house-fill me-1"></i>Home</a>
                    <?php if ($is_logged_in): ?>
                        <a href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                        <a href="pos.php"><i class="bi bi-cart-check me-1"></i>POS</a>
                        <a href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                    <?php else: ?>
                        <a href="staff_login.php" class="btn-cart"><i class="bi bi-people me-1"></i>Staff</a>
                        <a href="report/report.html" class="btn-cart"><i class="bi bi-check-circle"></i>Reports</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>Fresh <span>Products</span> Delivered</h1>
            <p>Shop the best quality products at the best prices</p>
            <form class="search-box" method="GET" action="index.php">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>
    </div>

    <div class="container py-4">
        <div class="row">
            <!-- Mobile Filter Toggle Button -->
            <button class="filter-toggle" onclick="toggleFilter()">
                <i class="bi bi-funnel-fill me-2"></i>Filter Products
            </button>

            <!-- Overlay for mobile -->
            <div class="filter-overlay" id="filterOverlay" onclick="toggleFilter()"></div>

            <!-- Sidebar Filters -->
            <div class="col-md-3">
                <div class="filter-sidebar" id="filterSidebar">
                    <span class="close-filter" onclick="toggleFilter()">&times;</span>
                    <h5><i class="bi bi-funnel-fill me-2"></i>Filters</h5>
                    <hr>

                    <div class="filter-section">
                        <h6>Categories</h6>
                        <ul class="category-list">
                            <li><a href="index.php" class="<?php echo $category == 0 ? 'active' : ''; ?>">All Products</a></li>
                            <?php foreach ($categories as $cat): ?>
                                <li><a href="?category=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $min_price > 0 ? '&min_price=' . $min_price : ''; ?><?php echo $max_price < 999999 ? '&max_price=' . $max_price : ''; ?>" class="<?php echo $category == $cat['id'] ? 'active' : ''; ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="filter-section">
                        <h6>Price Range (RWF)</h6>
                        <form method="GET" action="index.php" id="priceFilterForm">
                            <input type="hidden" name="category" value="<?php echo $category; ?>">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <div class="price-range">
                                <input type="number" name="min_price" placeholder="Min" value="<?php echo $min_price > 0 ? $min_price : ''; ?>" step="100">
                                <input type="number" name="max_price" placeholder="Max" value="<?php echo $max_price < 999999 ? $max_price : ''; ?>" step="100">
                            </div>
                            <button type="submit" class="btn btn-sm w-100 mt-2" style="background:#f3ca20; color:#000;">Apply Price</button>
                        </form>
                    </div>

                    <a href="index.php" class="btn btn-sm w-100 mt-2" style="background:#000; color:#f3ca20;">Clear Filters</a>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h4 class="mb-0" style="font-size: 1.2rem;"><?php echo count($products); ?> Products Found</h4>
                    <?php if (!empty($search)): ?>
                        <p class="text-muted mb-0 small">Search: "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
                    <?php endif; ?>
                </div>

                <?php if (empty($products)): ?>
                    <div class="text-center py-5 bg-white rounded-3">
                        <i class="bi bi-box-seam" style="font-size: 3rem; color: #ccc;"></i>
                        <h5 class="mt-3">No products found</h5>
                        <p class="small">Try adjusting your filters</p>
                        <a href="index.php" class="btn btn-sm" style="background:#000; color:#f3ca20;">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($products as $product): ?>
                            <div class="col-6 col-md-6 col-lg-4">
                                <div class="product-card">
                                    <div class="product-image" style="background-image: url('<?php echo $product['image_url'] ?: 'https://via.placeholder.com/300x200/f5f5f5/999?text=No+Image'; ?>')">
                                        <div class="product-price">Rwf <?php echo number_format($product['price'], 0); ?></div>
                                    </div>
                                    <div class="product-body">
                                        <div class="product-category"><i class="bi bi-tag me-1"></i><?php echo $product['category_name'] ?? 'Uncategorized'; ?></div>
                                        <div class="product-title">
                                            <a href="product_detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars(substr($product['name'], 0, 35)); ?></a>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted">Stock: <?php echo $product['stock_quantity']; ?></small>
                                            <?php if ($is_logged_in): ?>
                                                <a href="pos.php" class="btn-add-cart"><i class="bi bi-cart-plus me-1"></i>Buy</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile filter toggle function
        function toggleFilter() {
            const filterSidebar = document.getElementById('filterSidebar');
            const filterOverlay = document.getElementById('filterOverlay');

            filterSidebar.classList.toggle('show');
            filterOverlay.classList.toggle('show');

            // Prevent body scroll when filter is open
            if (filterSidebar.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Close filter when clicking on a category link (mobile)
        document.querySelectorAll('.category-list a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    toggleFilter();
                }
            });
        });

        // Close filter when submitting price form (mobile)
        const priceForm = document.getElementById('priceFilterForm');
        if (priceForm) {
            priceForm.addEventListener('submit', function() {
                if (window.innerWidth <= 768) {
                    // Let the form submit normally
                    // The filter will close after page reload
                }
            });
        }

        // Handle window resize - reset filter state
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const filterSidebar = document.getElementById('filterSidebar');
                const filterOverlay = document.getElementById('filterOverlay');
                filterSidebar.classList.remove('show');
                filterOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    </script>
</body>

</html>