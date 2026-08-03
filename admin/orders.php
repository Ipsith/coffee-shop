<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Administrator';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$allowedStatuses = ['Pending', 'Processing', 'Preparing', 'Out for Delivery', 'Completed', 'Cancelled'];

$message = '';
$messageType = 'success';

// Handle status update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $message = 'Your session expired, please try again.';
        $messageType = 'danger';
    } else {
        $orderId   = (int) ($_POST['order_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');

        if (!in_array(strtolower($newStatus), array_map('strtolower', $allowedStatuses), true)) {
            $message = 'Invalid status value.';
            $messageType = 'danger';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE orders SET status = :status, order_status = :order_status WHERE id = :id");
                $stmt->execute(['status' => $newStatus, 'order_status' => $newStatus, 'id' => $orderId]);
                $message = "Order #$orderId updated to \"{$newStatus}\".";
                $messageType = 'success';
            } catch (PDOException $e) {
                error_log('Order status update error: ' . $e->getMessage());
                $message = 'A database error occurred while updating the order.';
                $messageType = 'danger';
            }
        }
    }
}

// Fetch all orders using LEFT JOIN and COALESCE
try {
    $sql = "
        SELECT
            o.id,
            COALESCE(o.total_amount, 0) AS total_amount,
            COALESCE(o.order_status, o.status, 'Pending') AS status,
            o.created_at,
            COALESCE(u.username, u.name, 'Customer') AS customer_name,
            u.email AS customer_email,
            COALESCE(u.phone, 'N/A') AS customer_phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ";
    $orders = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Orders list error: ' . $e->getMessage());
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f1ee; }
        .navbar-brew { background-color: #4a2c2a; }
        .navbar-brew .nav-link { color: rgba(255,255,255,.85); }
        .navbar-brew .nav-link:hover, .navbar-brew .nav-link.active { color: #fff; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 .25rem .75rem rgba(0,0,0,.08); }
        .status-select { min-width: 150px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-brew mb-4">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">☕ Highland Roast Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link active" href="orders.php">Orders</a></li>
                <li class="nav-item">
                    <span class="nav-link text-white-50"><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></span>
                </li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <h3 class="mb-4">Manage Orders</h3>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Phone</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Update Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= (int) $o['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($o['customer_name'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($o['customer_email'])): ?>
                                    <div class="text-muted small"><?= htmlspecialchars($o['customer_email'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($o['customer_phone'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>Rs. <?= number_format((float) $o['total_amount'], 2) ?></td>
                            <td>
                                <?php
                                    $st = strtolower($o['status']);
                                    $badgeClass = match ($st) {
                                        'pending'          => 'bg-warning text-dark',
                                        'processing'       => 'bg-info text-dark',
                                        'preparing'        => 'bg-info text-dark',
                                        'out for delivery' => 'bg-primary',
                                        'completed', 'delivered' => 'bg-success',
                                        'cancelled'        => 'bg-danger',
                                        default            => 'bg-secondary',
                                    };
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars(ucfirst($o['status']), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($o['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end">
                                <form method="POST" action="orders.php" class="d-flex gap-2 justify-content-end">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                                    <select name="status" class="form-select form-select-sm status-select">
                                        <?php foreach ($allowedStatuses as $s): ?>
                                            <option value="<?= $s ?>" <?= strtolower($o['status']) === strtolower($s) ? 'selected' : '' ?>>
                                                <?= $s ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-dark">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
