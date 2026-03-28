<?php
    require_once 'config.php';
    requireLogin();

    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update_profile'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, $_SESSION['user_id']])) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $success = "Profile updated!";
            } else {
                $error = "Update failed";
            }
        }

        if (isset($_POST['change_password'])) {
            $current = $_POST['current_password'];
            $new = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];

            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!password_verify($current, $user['password'])) {
                $error = "Current password is incorrect";
            } elseif (strlen($new) < 6) {
                $error = "New password must be at least 6 characters";
            } elseif ($new !== $confirm) {
                $error = "Passwords do not match";
            } else {
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed, $_SESSION['user_id']])) {
                    $success = "Password changed successfully!";
                } else {
                    $error = "Failed to change password";
                }
            }
        }
    }
    ?>

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Profile - SuperMarket Pro</title>
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
             font-family: 'Segoe UI', sans-serif;
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
         }

         .sidebar-brand {
             padding: 25px 20px;
             text-align: center;
             border-bottom: 1px solid rgba(255, 255, 255, 0.1);
         }

         .sidebar-brand h3 {
             color: #FFA000;
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

         .profile-card {
             background: white;
             border-radius: 16px;
             padding: 30px;
             border: 1px solid #e0e0e0;
             margin-bottom: 30px;
         }

         .profile-avatar {
             width: 100px;
             height: 100px;
             background: #2E7D32;
             border-radius: 50%;
             display: flex;
             align-items: center;
             justify-content: center;
             margin: 0 auto 20px;
             font-size: 3rem;
             color: white;
         }

         .btn-save {
             background: #2E7D32;
             color: white;
             border: none;
             padding: 10px 30px;
             border-radius: 50px;
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
                 <li class="nav-item"><a href="profile.php" class="nav-link active"><i class="bi bi-person-circle"></i><span>Profile</span></a></li>
                 <li class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
             </ul>
         </div>

         <div class="main-content">
             <div class="profile-card text-center">
                 <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                 <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                 <p class="text-muted"><?php echo $_SESSION['user_email']; ?></p>
                 <span class="badge bg-<?php echo $_SESSION['user_role'] == 'admin' ? 'danger' : ($_SESSION['user_role'] == 'manager' ? 'warning' : 'info'); ?> fs-6 px-3 py-2"><?php echo ucfirst($_SESSION['user_role']); ?></span>
             </div>

             <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
             <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

             <div class="profile-card">
                 <h5><i class="bi bi-person-gear me-2"></i>Edit Profile</h5>
                 <form method="POST">
                     <div class="mb-3"><label>Full Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required></div>
                     <div class="mb-3"><label>Email Address</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required></div>
                     <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
                 </form>
             </div>

             <div class="profile-card">
                 <h5><i class="bi bi-key-fill me-2"></i>Change Password</h5>
                 <form method="POST">
                     <div class="mb-3">
                         <label>Current Password</label>
                         <div class="input-group">
                             <input type="password" name="current_password" class="form-control" id="current_password" required>
                             <span class="input-group-text" onclick="togglePassword('current_password', this)" style="cursor:pointer;">
                                 <i class="bi bi-eye-slash"></i>
                             </span>
                         </div>
                     </div>
                     <div class="mb-3">
                         <label>New Password</label>
                         <div class="input-group">
                             <input type="password" name="new_password" id="new_password" class="form-control" required>
                             <span class="input-group-text" onclick="togglePassword('new_password', this)" style="cursor:pointer;">
                                 <i class="bi bi-eye-slash"></i>
                             </span>
                         </div>
                     </div>
                     <div class="mb-3">
                         <label>Confirm New Password</label>
                         <div class="input-group">
                             <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                             <span class="input-group-text" onclick="togglePassword('confirm_password', this)" style="cursor:pointer;">
                                 <i class="bi bi-eye-slash"></i>
                             </span>
                         </div>
                         <div id="password-match-message" style="font-size: 0.95em; margin-top: 5px;"></div>
                     </div>
                     <button type="submit" name="change_password" class="btn-save">Change Password</button>
                 </form>
             </div>
         </div>
     </div>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
     <script>
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const message = document.getElementById('password-match-message');

    function checkPasswordMatch() {
        if (!confirmPassword.value) {
            message.textContent = '';
            return;
        }
        if (newPassword.value === confirmPassword.value) {
            message.textContent = 'Passwords match';
            message.style.color = 'green';
        } else {
            message.textContent = 'Passwords do not match';
            message.style.color = 'red';
        }
    }

    newPassword.addEventListener('input', checkPasswordMatch);
    confirmPassword.addEventListener('input', checkPasswordMatch);
});

function togglePassword(passwordFieldId, toggleIcon) {
    const passwordField = document.getElementById(passwordFieldId);
    const fieldType = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', fieldType);
    toggleIcon.querySelector('i').classList.toggle('bi-eye');
    toggleIcon.querySelector('i').classList.toggle('bi-eye-slash');
}
</script>
 </body>

 </html>