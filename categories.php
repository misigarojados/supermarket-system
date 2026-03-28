<?php
require_once 'config.php';
requireAdmin();

$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$_GET['delete']])) $message = "Category deleted successfully!";
    else $error = "Failed to delete category.";
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE categories SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    $message = "Category status updated!";
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'];
    $description = $_POST['description'];

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
        if ($stmt->execute([$name, $description, $id])) $message = "Category updated!";
        else $error = "Update failed";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        if ($stmt->execute([$name, $description])) $message = "Category added!";
        else $error = "Add failed";
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - SuperMarket Pro</title>
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
            padding: 10px 20px;
            border-radius: 50px;
            border: none;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            background: #f3ca20;
            color: #000;
            transform: translateY(-2px);
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
                    <h1><i class="bi bi-tags me-2"></i>Categories</h1>
                    <p>Manage product categories</p>
                </div>
                <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()"><i class="bi bi-plus-circle me-2"></i>Add Category</button>
            </div>

            <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <div class="bg-white rounded-3 p-4 border">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td>#<?php echo $c['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['description']); ?></td>
                                <td><span class="badge bg-<?php echo $c['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo $c['status']; ?></span></td>
                                <td>
                                    <button class="btn btn-sm" style="background:#f3ca20; color:#000;" onclick="editCategory(<?php echo htmlspecialchars(json_encode($c)); ?>)"><i class="bi bi-pencil"></i></button>
                                    <a href="?toggle=<?php echo $c['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-<?php echo $c['status'] == 'active' ? 'pause' : 'play'; ?>"></i></a>
                                    <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background:#000; color:#f3ca20;">
                    <h5>Category Details</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="categoryId">
                        <div class="mb-3"><label>Category Name *</label><input type="text" name="name" id="categoryName" class="form-control" required></div>
                        <div class="mb-3"><label>Description</label><textarea name="description" id="categoryDesc" class="form-control" rows="3"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn" style="background:#000; color:#f3ca20;">Save Category</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function resetForm() {
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDesc').value = '';
        }

        function editCategory(c) {
            document.getElementById('categoryId').value = c.id;
            document.getElementById('categoryName').value = c.name;
            document.getElementById('categoryDesc').value = c.description;
            new bootstrap.Modal(document.getElementById('categoryModal')).show();
        }

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