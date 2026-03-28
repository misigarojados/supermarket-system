<?php
require_once 'config.php';
requireManager();

$message = '';
$error = '';

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = $_POST['product_id'];
    $new_stock = $_POST['stock_quantity'];
    $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
    if ($stmt->execute([$new_stock, $id])) $message = "Stock updated successfully!";
    else $error = "Update failed";
}

// Get all products with stock info
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.stock_quantity ASC")->fetchAll();
?>

<?php include 'header.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-header">
        <h1><i class="bi bi-clipboard-data me-2"></i>Inventory Management</h1>
        <p>Track and update product stock levels</p>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

    <div class="bg-white rounded-3 p-4 border">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Min Stock Level</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p):
                        $stock_status = '';
                        $status_class = '';
                        if ($p['stock_quantity'] <= 0) {
                            $stock_status = 'Out of Stock';
                            $status_class = 'stock-critical';
                        } elseif ($p['stock_quantity'] <= $p['min_stock_level']) {
                            $stock_status = 'Low Stock';
                            $status_class = 'stock-low';
                        } else {
                            $stock_status = 'In Stock';
                            $status_class = '';
                        }
                    ?>
                        <tr class="<?php echo $status_class; ?>">
                            <td>#<?php echo $p['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                            <td><?php echo $p['category_name']; ?></td>
                            <td class="fw-bold"><?php echo $p['stock_quantity']; ?> <?php echo $p['unit']; ?></td>
                            <td><?php echo $p['min_stock_level']; ?> <?php echo $p['unit']; ?></td>
                            <td><span class="badge bg-<?php echo $p['stock_quantity'] <= 0 ? 'danger' : ($p['stock_quantity'] <= $p['min_stock_level'] ? 'warning' : 'success'); ?>"><?php echo $stock_status; ?></span></td>
                            <td>
                                <button class="btn btn-sm" style="background:#f3ca20; color:#000;" data-bs-toggle="modal" data-bs-target="#stockModal" onclick="setStockData(<?php echo $p['id']; ?>, '<?php echo addslashes($p['name']); ?>', <?php echo $p['stock_quantity']; ?>)">
                                    <i class="bi bi-pencil"></i> Update Stock
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#000; color:#f3ca20;">
                <h5>Update Stock</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="stockProductId">
                    <div class="mb-3"><label>Product</label><input type="text" id="stockProductName" class="form-control" readonly></div>
                    <div class="mb-3"><label>New Stock Quantity</label><input type="number" name="stock_quantity" id="stockQuantity" class="form-control" required min="0"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_stock" class="btn" style="background:#000; color:#f3ca20;">Update Stock</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    function setStockData(id, name, stock) {
        document.getElementById('stockProductId').value = id;
        document.getElementById('stockProductName').value = name;
        document.getElementById('stockQuantity').value = stock;
    }
</script>