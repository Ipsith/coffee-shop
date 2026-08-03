<?php
require_once '../config/db.php';

$email = 'admin@coffeeshop.com';
$password = 'admin123';

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Diagnostic Test Results:</h3>";

if ($user) {
    echo "✅ User found in Database!<br>";
    echo "Name: " . $user['name'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    
    if (password_verify($password, $user['password'])) {
        echo "✅ <b style='color:green;'>PASSWORD MATCHES SUCCESSFUL!</b>";
    } else {
        echo "❌ <b style='color:red;'>PASSWORD MATCH FAILED!</b> (Database Hash is incorrect)";
    }
} else {
    echo "❌ <b style='color:red;'>User NOT found in database!</b>";
}
?>