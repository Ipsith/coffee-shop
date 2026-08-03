<?php
session_start();
require_once 'config/db.php';

// PHPMailer Classes Import කිරීම
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/libs/PHPMailer/Exception.php';
require __DIR__ . '/libs/PHPMailer/PHPMailer.php';
require __DIR__ . '/libs/PHPMailer/SMTP.php';

// Customer Login වී නැත්නම් Login පිටුවට Redirect කිරීම
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Session එකේ Cart එක හිස් නම් cart.php එකට Redirect කිරීම
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// 1. Cart එකේ ඇති Products Database එකෙන් ලබා ගැනීම
$cart_items = [];
$total_amount = 0;

$product_ids = array_keys($_SESSION['cart']);
$product_ids = array_filter($product_ids, 'is_numeric');

if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $pid = $product['id'];
        $cart_val = $_SESSION['cart'][$pid];
        $quantity = is_array($cart_val) ? intval($cart_val['qty'] ?? 1) : intval($cart_val);
        $price = floatval($product['price']);
        $subtotal = $price * $quantity;
        
        $total_amount += $subtotal;

        $cart_items[] = [
            'id'       => $pid,
            'name'     => $product['name'],
            'price'    => $price,
            'qty'      => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

// 2. Customer ගේ Email & Name Database එකෙන් ලබා ගැනීම
$user_email = 'customer@example.com';
$user_name  = 'Valued Customer';

try {
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_email = $user['email'] ?? $user_email;
        $user_name = $user['username'] ?? $user['name'] ?? $user['full_name'] ?? $user_name;
    }
} catch (Exception $e) {
    // Ignore user fetch errors
}

// 3. Order එක Confirm කළ විට
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    if ($total_amount > 0) {
        try {
            $conn->beginTransaction();

            // Orders Table එකට Insert කිරීම
            $stmt = $conn->prepare("
                INSERT INTO orders (user_id, total_amount, payment_status, order_status) 
                VALUES (?, ?, 'Pending (COD)', 'Processing')
            ");
            $stmt->execute([$user_id, $total_amount]);
            $order_id = $conn->lastInsertId();

            // Order Items Table එකට Insert කිරීම
            $item_stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_name, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");

            $items_html = "";
            foreach ($cart_items as $item) {
                $item_stmt->execute([$order_id, $item['name'], $item['qty'], $item['price']]);
                $subtotal_fmt = number_format($item['subtotal'], 2);
                $items_html .= "<tr>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['name']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['qty']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>Rs. {$subtotal_fmt}</td>
                </tr>";
            }

            // Commit the DB transaction & clear cart first
            $conn->commit();
            unset($_SESSION['cart']);

            // Email එක යැවීම (Email Error එකක් ආවත් Order එක Save වේ)
            try {
                $mail = new PHPMailer(true);

                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ipsithofficial@gmail.com'; 
                $mail->Password   = 'xpqe ioqz efsj uxvu'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->setFrom('ipsithofficial@gmail.com', 'Highland Roast Coffee');
                $mail->addAddress($user_email, $user_name);

                $mail->isHTML(true);
                $mail->Subject = "Order Confirmation #{$order_id} - Highland Roast ☕";
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                        <h2 style='color: #6f4e37; text-align: center;'>☕ Highland Roast Coffee</h2>
                        <h3>Thank you for your order, {$user_name}!</h3>
                        <p>We are brewing your order right now. Here is your receipt:</p>
                        <p><strong>Order ID:</strong> #{$order_id}</p>
                        <p><strong>Payment Method:</strong> Cash on Delivery (COD)</p>
                        <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                            <thead>
                                <tr style='background-color: #6f4e37; color: white;'>
                                    <th style='padding: 8px; text-align: left;'>Item</th>
                                    <th style='padding: 8px; text-align: left;'>Qty</th>
                                    <th style='padding: 8px; text-align: left;'>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>{$items_html}</tbody>
                        </table>
                        <h3 style='text-align: right; color: #6f4e37; margin-top: 15px;'>Total: Rs. " . number_format($total_amount, 2) . "</h3>
                        <hr>
                        <p style='text-align: center; color: #777; font-size: 12px;'>Highland Roast Coffee Shop &copy; 2026</p>
                    </div>";

                $mail->send();
            } catch (Exception $mailException) {
                // Email yawiime error ekak awath order eka database meke save vi thibee
                error_log("Order confirmation email failed: " . $mailException->getMessage());
            }

            header("Location: my_orders.php?success=1");
            exit;

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            // Error එක Screen එකේ Red alert එකකින් පෙන්වයි
            $error = "Order Error: " . $e->getMessage();
        }
    } else {
        $error = "Your cart is empty!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Cash on Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f5f0; }
        .card-custom { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .btn-brew { background-color: #6f4e37; color: white; }
        .btn-brew:hover { background-color: #4a2c2a; color: white; }
    </style>
</head>
<body>

<div class="container py-5" style="max-width: 600px;">
    <div class="card card-custom p-4 bg-white">
        <h3 class="text-center mb-4" style="color: #4a2c2a;">☕ Order Checkout</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h5 class="mb-3">Order Summary</h5>
        <ul class="list-group mb-3">
            <?php foreach ($cart_items as $item): ?>
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                        <small class="text-muted">Quantity: <?php echo $item['qty']; ?></small>
                    </div>
                    <span class="text-muted">Rs. <?php echo number_format($item['subtotal'], 2); ?></span>
                </li>
            <?php endforeach; ?>
            <li class="list-group-item d-flex justify-content-between bg-light">
                <span class="fw-bold">Total (LKR)</span>
                <strong style="color: #6f4e37;">Rs. <?php echo number_format($total_amount, 2); ?></strong>
            </li>
        </ul>

        <div class="alert alert-warning mb-3">
            <strong>Payment Method:</strong> Cash on Delivery (COD) <br>
            <small>You can pay in cash when your coffee order is delivered to your doorstep.</small>
        </div>

        <form method="POST" action="checkout.php">
            <button type="submit" name="confirm_order" class="btn btn-brew w-100 py-2 fs-5">
                Confirm & Place Order ☕
            </button>
        </form>
    </div>
</div>

</body>
</html>