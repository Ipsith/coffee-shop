<?php
session_start();
require_once 'config/db.php';

// Customer Login වී නැත්නම් Login පිටුවට Redirect කිරීම
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Login වී සිටින Customer ගේ Orders සහ ඒවයේ Items Retrieve කරගැනීම
$stmt = $conn->prepare("
    SELECT o.id AS order_id, o.total_amount, o.payment_status, o.order_status, o.created_at
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.id DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders & Live Tracking - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f5f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-coffee {
            background-color: #4a2c2a;
        }
        .badge-processing { background-color: #ffc107; color: #000; }
        .badge-preparing { background-color: #17a2b8; }
        .badge-out-for-delivery { background-color: #0d6efd; }
        .badge-delivered { background-color: #198754; }
        .badge-cancelled { background-color: #dc3545; }
        .card-order {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-coffee px-4">
    <a class="navbar-brand font-weight-bold" href="index.php">☕ Highland Roast</a>
    <div class="ms-auto">
        <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?>!</span>
        <a href="index.php" class="btn btn-outline-light btn-sm me-2">Menu</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="container py-5">
    <h2 class="mb-4" style="color: #4a2c2a;">📦 My Orders & Live Tracking</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info text-center">
            You haven't placed any orders yet. <a href="index.php" class="alert-link">Browse our coffee menu!</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <?php
                    // Order Items ලබාගැනීම
                    $item_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $item_stmt->execute([$order['order_id']]);
                    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Badge Color එක සකස් කිරීම
                    $status_class = 'badge-processing';
                    $status = strtolower($order['order_status']);
                    if ($status === 'preparing') $status_class = 'badge-preparing';
                    elseif ($status === 'out for delivery') $status_class = 'badge-out-for-delivery';
                    elseif ($status === 'delivered' || $status === 'completed') $status_class = 'badge-delivered';
                    elseif ($status === 'cancelled') $status_class = 'badge-cancelled';
                ?>
                <div class="col-12 mb-4">
                    <div class="card card-order p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <div>
                                <h5 class="mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                <small class="text-muted">Placed on: <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div>
                                <span class="badge <?php echo $status_class; ?> p-2 fs-6">
                                    Status: <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Order Items List -->
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td class="text-end">Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                            <div>
                                <span>Payment Status: </span>
                                <strong class="<?php echo ($order['payment_status'] === 'Paid') ? 'text-success' : 'text-warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </strong>
                            </div>
                            <div>
                                <h5>Total: <span style="color: #6f4e37;">Rs. <?php echo number_format($order['total_amount'], 2); ?></span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>