<?php
session_start();
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form එකෙන් එන දත්ත වල අමතර spaces ඉවත් කරගැනීම
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        // 1. Email එක අනුව User කෙනෙක් සිටීදැයි පරීක්ෂා කිරීම
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. User සිටී නම්, Password එක සහ Role එක Admin ද යන්න පරීක්ෂා කිරීම
        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $user['id'];
                $_SESSION['admin_name']      = $user['name'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Access denied! You are not an admin.';
            }
        } else {
            $error = 'Invalid email address or password!';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #4a2c2a 0%, #6f4e37 100%);
        }
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.25);
        }
        .login-card .card-header {
            background-color: #4a2c2a;
            color: #fff;
            border-radius: 1rem 1rem 0 0 !important;
        }
        .btn-brew {
            background-color: #6f4e37;
            border-color: #6f4e37;
            color: #fff;
        }
        .btn-brew:hover {
            background-color: #4a2c2a;
            border-color: #4a2c2a;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 420px;">
    <div class="card login-card shadow">
        <div class="card-header text-center py-3">
            <h4 class="mb-0">☕ Admin Portal</h4>
        </div>
        <div class="card-body p-4">

            <?php if ($error): ?>
                <div class="alert alert-danger text-center py-2 mb-3"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@coffeeshop.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-brew w-100 py-2 mt-2">Login to Dashboard</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>