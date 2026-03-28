<?php
require_once 'config.php';
requireLogin();

$message = '';
$error = '';

// Get products for POS
$products = $pdo->query("SELECT id, name, barcode, price, stock_quantity FROM products WHERE status='active' AND stock_quantity > 0 ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale - SuperMarket Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #F5F5F5;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: #1B5E20;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: all 0.3s;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-brand h3 {
            color: #FFA000;
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
            background: rgba(255, 160, 0, 0.2);
            color: #FFA000;
        }

        .nav-link.active {
            background: #FFA000;
            color: #1B5E20;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px 40px;
        }

        .cart-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            height: 100%;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            transition: all 0.3s;
            cursor: pointer;
            border: 1px solid #e0e0e0;
            margin-bottom: 15px;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: #FFA000;
        }

        .cart-item {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .btn-checkout {
            background: #2E7D32;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
        }

        .btn-checkout:hover {
            background: #1B5E20;
        }

        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.7rem;
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
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <div class="sidebar">
            <div class="sidebar-brand">
                <h3><i class="bi bi-cart-fill"></i> SuperMarket</h3>
                <p><?php echo ucfirst($_SESSION['user_role']); ?> Panel</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                <li class="nav-item"><a href="pos.php" class="nav-link active"><i class="bi bi-cart-check"></i><span>Point of Sale</span></a></li>
                <li class="nav-item"><a href="products.php" class="nav-link"><i class="bi bi-box-seam"></i><span>Products</span></a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="bi bi-person-circle"></i><span>Profile</span></a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-md-8">
                    <div class="cart-card">
                        <h5><i class="bi bi-search me-2"></i>Search & Select Products</h5>
                        <input type="text" id="searchProduct" class="form-control mb-3" placeholder="Search by name or barcode...">
                        <div id="productList" class="row">
                            <?php foreach ($products as $p): ?>
                                <div class="col-md-6">
                                    <div class="product-card" onclick="addToCart(<?php echo $p['id']; ?>, '<?php echo addslashes($p['name']); ?>', <?php echo $p['price']; ?>, <?php echo $p['stock_quantity']; ?>)">
                                        <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                        <div class="d-flex justify-content-between mt-2">
                                            <span class="text-success fw-bold">Rwf <?php echo number_format($p['price'], 2); ?></span>
                                            <small class="text-muted">Stock: <?php echo $p['stock_quantity']; ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="cart-card">
                        <h5><i class="bi bi-cart-fill me-2"></i>Shopping Cart</h5>
                        <div id="cartItems"></div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2"><strong>Subtotal:</strong> <span id="subtotal">Rwf 0.00</span></div>
                        <div class="d-flex justify-content-between mb-2"><strong>Tax (10%):</strong> <span id="tax">Rwf 0.00</span></div>
                        <div class="d-flex justify-content-between mb-3"><strong class="fs-5">Total:</strong> <strong class="fs-5 text-success" id="total">Rwf 0.00</strong></div>
                        <input type="text" id="customerName" class="form-control mb-2" placeholder="Customer Name (optional)">
                        <select id="paymentMethod" class="form-select mb-3">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                        <button class="btn-checkout" onclick="checkout()"><i class="bi bi-check-circle-fill me-2"></i>Complete Sale</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        function addToCart(id, name, price, stock) {
            let existing = cart.find(item => item.id === id);
            if (existing) {
                if (existing.quantity < stock) {
                    existing.quantity++;
                } else {
                    alert('Insufficient stock!');
                    return;
                }
            } else {
                cart.push({
                    id,
                    name,
                    price,
                    quantity: 1,
                    stock
                });
            }
            updateCartDisplay();
        }

        function updateQuantity(id, change) {
            let item = cart.find(i => i.id === id);
            if (item) {
                let newQty = item.quantity + change;
                if (newQty < 1) {
                    cart = cart.filter(i => i.id !== id);
                } else if (newQty <= item.stock) {
                    item.quantity = newQty;
                } else {
                    alert('Insufficient stock!');
                }
            }
            updateCartDisplay();
        }

        function updateCartDisplay() {
            let subtotal = 0;
            let html = '';
            cart.forEach(item => {
                let itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                html += `<div class="cart-item d-flex justify-content-between align-items-center">
                            <div><strong>${item.name}</strong><br><small>Rwf ${item.price} x ${item.quantity}</small></div>
                            <div><span class="fw-bold">Rwf ${itemTotal.toFixed(2)}</span>
                            <button class="btn-remove ms-2" onclick="updateQuantity(${item.id}, -1)"><i class="bi bi-dash"></i></button>
                            <button class="btn-remove ms-1" onclick="updateQuantity(${item.id}, 1)"><i class="bi bi-plus"></i></button></div>
                        </div>`;
            });
            document.getElementById('cartItems').innerHTML = html || '<p class="text-muted text-center">Cart is empty</p>';
            let tax = subtotal * 0.1;
            let total = subtotal + tax;
            document.getElementById('subtotal').innerHTML = 'Rwf ' + subtotal.toFixed(2);
            document.getElementById('tax').innerHTML = 'Rwf ' + tax.toFixed(2);
            document.getElementById('total').innerHTML = 'Rwf ' + total.toFixed(2);
        }

        function checkout() {
            if (cart.length === 0) {
                alert('Cart is empty!');
                return;
            }
            let data = {
                cart: cart,
                customer: document.getElementById('customerName').value,
                payment: document.getElementById('paymentMethod').value
            };
            fetch('process_sale.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.json()).then(result => {
                    if (result.success) {
                        alert('Sale completed! Invoice: ' + result.invoice);
                        cart = [];
                        updateCartDisplay();
                        document.getElementById('customerName').value = '';
                        location.reload();
                    } else {
                        alert('Error: ' + result.error);
                    }
                });
        }

        document.getElementById('searchProduct').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                let text = card.innerText.toLowerCase();
                card.parentElement.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>

</html>