<?php
require_once 'config.php';
requireManager();
$page_title = 'Products - SuperMarket Pro';

$message = '';
$error = '';

// Create uploads directory if not exists
$upload_dir = 'uploads/products/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $product = $stmt->fetch();
    if ($product && $product['image_url'] && file_exists($product['image_url'])) {
        unlink($product['image_url']);
    }
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$_GET['delete']])) $message = "Product deleted successfully!";
    else $error = "Failed to delete product.";
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE products SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    $message = "Product status updated!";
}

// Handle add/edit with image
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'];
    $barcode = $_POST['barcode'];
    $category_id = $_POST['category_id'] ?: null;
    $supplier_id = $_POST['supplier_id'] ?: null;
    $price = $_POST['price'];
    $cost_price = $_POST['cost_price'];
    $stock_quantity = $_POST['stock_quantity'];
    $min_stock_level = $_POST['min_stock_level'];
    $unit = $_POST['unit'];
    $description = $_POST['description'];

    // Handle image upload
    $image_url = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $file_type = $_FILES['product_image']['type'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($file_type, $allowed)) {
            if ($_FILES['product_image']['size'] <= $max_size) {
                $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $ext;
                $upload_path = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                    $image_url = $upload_path;
                    // Delete old image if exists
                    if ($id > 0) {
                        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
                        $stmt->execute([$id]);
                        $old = $stmt->fetch();
                        if ($old && $old['image_url'] && file_exists($old['image_url'])) {
                            unlink($old['image_url']);
                        }
                    }
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Image must be less than 2MB.";
            }
        } else {
            $error = "Only JPG, PNG, WebP images allowed.";
        }
    }

    if ($id > 0) {
        if ($image_url) {
            $stmt = $pdo->prepare("UPDATE products SET name=?, barcode=?, category_id=?, supplier_id=?, price=?, cost_price=?, stock_quantity=?, min_stock_level=?, unit=?, description=?, image_url=? WHERE id=?");
            if ($stmt->execute([$name, $barcode, $category_id, $supplier_id, $price, $cost_price, $stock_quantity, $min_stock_level, $unit, $description, $image_url, $id]))
                $message = "Product updated successfully!";
            else $error = "Failed to update product.";
        } else {
            $stmt = $pdo->prepare("UPDATE products SET name=?, barcode=?, category_id=?, supplier_id=?, price=?, cost_price=?, stock_quantity=?, min_stock_level=?, unit=?, description=? WHERE id=?");
            if ($stmt->execute([$name, $barcode, $category_id, $supplier_id, $price, $cost_price, $stock_quantity, $min_stock_level, $unit, $description, $id]))
                $message = "Product updated successfully!";
            else $error = "Failed to update product.";
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, barcode, category_id, supplier_id, price, cost_price, stock_quantity, min_stock_level, unit, description, image_url) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        if ($stmt->execute([$name, $barcode, $category_id, $supplier_id, $price, $cost_price, $stock_quantity, $min_stock_level, $unit, $description, $image_url]))
            $message = "Product added successfully!";
        else $error = "Failed to add product.";
    }
}

$products = $pdo->query("SELECT p.*, c.name as category_name, s.name as supplier_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN suppliers s ON p.supplier_id = s.id ORDER BY p.name")->fetchAll();
$categories = $pdo->query("SELECT id, name FROM categories WHERE status='active'")->fetchAll();
$suppliers = $pdo->query("SELECT id, name FROM suppliers WHERE status='active'")->fetchAll();
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - SuperMarket Pro</title>
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
            background: #000000;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: all 0.3s;
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
            transition: all 0.3s;
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

        .btn-primary-custom {
            background: #000000;
            color: #f3ca20;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            border: none;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            background: #f3ca20;
            color: #000000;
            transform: translateY(-2px);
        }

        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        .image-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 10px;
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


        <div class="main-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-box-seam me-2"></i>Products</h1>
                    <p>Manage your inventory</p>
                </div>
                <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()"><i class="bi bi-plus-circle me-2"></i>Add Product</button>
            </div>

            <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <div class="bg-white rounded-3 p-4 border">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Barcode</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td><?php if ($p['image_url']): ?><img src="<?php echo $p['image_url']; ?>" class="product-img"><?php else: ?><i class="bi bi-image" style="font-size: 2rem; color: #ccc;"></i><?php endif; ?></td>
                                    <td>#<?php echo $p['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                    <td><?php echo $p['barcode']; ?></td>
                                    <td><?php echo $p['category_name']; ?></td>
                                    <td class="text-success fw-bold">Rwf <?php echo number_format($p['price'], 2); ?></td>
                                    <td class="<?php echo $p['stock_quantity'] <= $p['min_stock_level'] ? 'text-danger fw-bold' : ''; ?>"><?php echo $p['stock_quantity']; ?></td>
                                    <td><span class="badge bg-<?php echo $p['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo $p['status']; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm" style="background:#f3ca20; color:#000;" onclick="editProduct(<?php echo htmlspecialchars(json_encode($p)); ?>)"><i class="bi bi-pencil"></i></button>
                                        <a href="?toggle=<?php echo $p['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-<?php echo $p['status'] == 'active' ? 'pause-circle' : 'play-circle'; ?>"></i></a>
                                        <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal with Image Upload -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background:#000000; color:#f3ca20;">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Product Details</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="productId">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label>Product Name *</label><input type="text" name="name" id="productName" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label>Barcode</label><input type="text" name="barcode" id="productBarcode" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label>Category</label><select name="category_id" id="productCategory" class="form-select">
                                    <option value="">Select Category</option><?php foreach ($categories as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="col-md-6 mb-3"><label>Supplier</label><select name="supplier_id" id="productSupplier" class="form-select">
                                    <option value="">Select Supplier</option><?php foreach ($suppliers as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="col-md-3 mb-3"><label>Selling Price *</label><input type="number" step="0.01" name="price" id="productPrice" class="form-control" required></div>
                            <div class="col-md-3 mb-3"><label>Cost Price</label><input type="number" step="0.01" name="cost_price" id="productCost" class="form-control"></div>
                            <div class="col-md-3 mb-3"><label>Stock Quantity *</label><input type="number" name="stock_quantity" id="productStock" class="form-control" required></div>
                            <div class="col-md-3 mb-3"><label>Min Stock Level</label><input type="number" name="min_stock_level" id="productMinStock" class="form-control" value="5"></div>
                            <div class="col-md-6 mb-3"><label>Unit</label><input type="text" name="unit" id="productUnit" class="form-control" placeholder="piece, kg, liter"></div>
                            <div class="col-md-6 mb-3"><label>Product Image</label><input type="file" name="product_image" id="productImage" class="form-control" accept="image/*" onchange="previewImage(this)">
                                <div id="imagePreview"></div>
                            </div>
                            <div class="col-md-12 mb-3"><label>Description</label><textarea name="description" id="productDesc" class="form-control" rows="2"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" style="background:#000000; color:#f3ca20;">Save Product</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').innerHTML = '<img src="' + e.target.result + '" class="image-preview">';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function resetForm() {
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productBarcode').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productStock').value = '';
            document.getElementById('productMinStock').value = '5';
            document.getElementById('imagePreview').innerHTML = '';
        }

        function editProduct(p) {
            document.getElementById('productId').value = p.id;
            document.getElementById('productName').value = p.name;
            document.getElementById('productBarcode').value = p.barcode;
            document.getElementById('productCategory').value = p.category_id || '';
            document.getElementById('productSupplier').value = p.supplier_id || '';
            document.getElementById('productPrice').value = p.price;
            document.getElementById('productCost').value = p.cost_price;
            document.getElementById('productStock').value = p.stock_quantity;
            document.getElementById('productMinStock').value = p.min_stock_level;
            document.getElementById('productUnit').value = p.unit;
            document.getElementById('productDesc').value = p.description;
            document.getElementById('imagePreview').innerHTML = p.image_url ? '<img src="' + p.image_url + '" class="image-preview">' : '';
            new bootstrap.Modal(document.getElementById('productModal')).show();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>