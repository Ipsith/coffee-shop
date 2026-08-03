<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'customer'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password!';
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
    <title>Customer Login - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f5f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card-custom {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .btn-coffee {
            background-color: #6f4e37;
            color: #fff;
        }
        .btn-coffee:hover {
            background-color: #4a2c2a;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 420px;">
    <div class="card card-custom p-4 bg-white">
        <h3 class="text-center mb-4" style="color: #4a2c2a;">☕ Customer Login</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="name@example.com">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-coffee w-100 py-2">Login</button>
        </form>

        <p class="text-center mt-3 mb-0">
            Don't have an account? <a href="register.php" style="color: #6f4e37;">Register here</a>
        </p>
    </div>
</div>

</body>
</html>