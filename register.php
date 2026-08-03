<?php
session_start();
require_once 'config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Email එක කලින් භාවිත කර ඇත්දැයි පරීක්ෂා කිරීම
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "This email is already registered!";
        } else {
            // Password එක Encrypt කිරීම
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // User තොරතුරු එකතු කිරීම (role = 'customer')
            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
            
            if ($insert_stmt->execute([$name, $email, $phone, $hashed_password])) {
                $message = "Registration successful! <a href='login.php' class='alert-link'>Click here to Login</a>";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Register - Coffee Shop</title>
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

<div class="container" style="max-width: 450px;">
    <div class="card card-custom p-4 bg-white">
        <h3 class="text-center mb-4" style="color: #4a2c2a;">☕ Create Account</h3>

        <?php if ($message): ?>
            <div class="alert alert-success text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required placeholder="John Doe">
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required placeholder="name@example.com">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="0771234567">
            </div>
            <div class="mb-3">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-coffee w-100 py-2">Register</button>
        </form>

        <p class="text-center mt-3 mb-0">
            Already have an account? <a href="login.php" style="color: #6f4e37;">Login here</a>
        </p>
    </div>
</div>

</body>
</html>