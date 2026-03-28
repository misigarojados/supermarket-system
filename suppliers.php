<?php

require_once 'config.php';
requireAdmin();

$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    if ($stmt->execute([$_GET['delete']])) $message = "Supplier deleted successfully!";
    else $error = "Failed to delete supplier.";
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE suppliers SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    $message = "Supplier status updated!";
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'];
    $contact_person = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=? WHERE id=?");
        if ($stmt->execute([$name, $contact_person, $phone, $email, $address, $id])) $message = "Supplier updated!";
        else $error = "Update failed";
    } else {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $contact_person, $phone, $email, $address])) $message = "Supplier added!";
        else $error = "Add failed";
    }
}

$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - SuperMarket Pro</title>
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
                    <h1><i class="bi bi-truck me-2"></i>Suppliers</h1>
                    <p>Manage product suppliers</p>
                </div>
                <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#supplierModal" onclick="resetForm()"><i class="bi bi-plus-circle me-2"></i>Add Supplier</button>
            </div>

            <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <div class="bg-white rounded-3 p-4 border">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $s): ?>
                            <tr>
                                <td>#<?php echo $s['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($s['contact_person']); ?></td>
                                <td><?php echo $s['phone']; ?></td>
                                <td><?php echo $s['email']; ?></td>
                                <td><span class="badge bg-<?php echo $s['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo $s['status']; ?></span></td>
                                <td>
                                    <button class="btn btn-sm" style="background:#f3ca20; color:#000;" onclick="editSupplier(<?php echo htmlspecialchars(json_encode($s)); ?>)"><i class="bi bi-pencil"></i></button>
                                    <a href="?toggle=<?php echo $s['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-<?php echo $s['status'] == 'active' ? 'pause' : 'play'; ?>"></i></a>
                                    <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this supplier?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="supplierModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background:#000; color:#f3ca20;">
                    <h5>Supplier Details</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="supplierId">
                        <div class="mb-3"><label>Supplier Name *</label><input type="text" name="name" id="supplierName" class="form-control" required></div>
                        <div class="mb-3"><label>Contact Person</label><input type="text" name="contact_person" id="supplierContact" class="form-control"></div>
                        <div class="mb-3"><label>Phone</label><input type="text" name="phone" id="supplierPhone" class="form-control"></div>
                        <div class="mb-3"><label>Email</label><input type="email" name="email" id="supplierEmail" class="form-control"></div>
                        <div class="mb-3"><label>Address</label><textarea name="address" id="supplierAddress" class="form-control" rows="2"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn" style="background:#000; color:#f3ca20;">Save Supplier</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>

    <script>
        function resetForm() {
            document.getElementById('supplierId').value = '';
            document.getElementById('supplierName').value = '';
            document.getElementById('supplierContact').value = '';
            document.getElementById('supplierPhone').value = '';
            document.getElementById('supplierEmail').value = '';
            document.getElementById('supplierAddress').value = '';
        }

        function editSupplier(s) {
            document.getElementById('supplierId').value = s.id;
            document.getElementById('supplierName').value = s.name;
            document.getElementById('supplierContact').value = s.contact_person;
            document.getElementById('supplierPhone').value = s.phone;
            document.getElementById('supplierEmail').value = s.email;
            document.getElementById('supplierAddress').value = s.address;
            new bootstrap.Modal(document.getElementById('supplierModal')).show();
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