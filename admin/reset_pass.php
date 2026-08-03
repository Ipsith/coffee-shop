<?php
require_once '../config/db.php';

// අලුත් Password එක Hash කිරීම
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$email = 'admin@coffeeshop.com';

// Database එකේ Password එක Update කිරීම
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
if ($stmt->execute([$hashed_password, $email])) {
    echo "<h2 style='color:green;'>✅ Success! Password has been updated to: <b>admin123</b></h2>";
    echo "<p><a href='login.php'>Click here to Login now</a></p>";
} else {
    echo "<h2 style='color:red;'>❌ Failed to update password.</h2>";
}
?>