<?php
/**
 * admin/dashboard.php
 * ---------------------------------------------------------
 * Admin landing page. Protected by session check.
 * Shows quick summary metrics: total products, total orders,
 * total revenue.
 * ---------------------------------------------------------
 */

require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Administrator';

// --- Fetch summary metrics ---
try {
    $totalProducts = (int) $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalOrders   = (int) $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();

    // Revenue is counted from orders that are not cancelled.
    $totalRevenue = (float) $conn->query(
        "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE LOWER(status) != 'cancelled' AND LOWER(COALESCE(order_status, '')) != 'cancelled'"
    )->fetchColumn();

    $pendingOrders = (int) $conn->query(
        "SELECT COUNT(*) FROM orders WHERE LOWER(status) IN ('pending', 'processing', 'preparing') OR LOWER(COALESCE(order_status, '')) IN ('pending', 'processing', 'preparing')"
    )->fetchColumn();
} catch (PDOException $e) {
    error_log('Dashboard metrics error: ' . $e->getMessage());
    $totalProducts = $totalOrders = $pendingOrders = 0;
    $totalRevenue  = 0.0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f1ee; }
        .navbar-brand { font-weight: 600; }
        .navbar-brew { background-color: #4a2c2a; }
        .navbar-brew .nav-link { color: rgba(255,255,255,.85); }
        .navbar-brew .nav-link:hover, .navbar-brew .nav-link.active { color: #fff; }
        .metric-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 .25rem .75rem rgba(0,0,0,.08);
        }
        .metric-icon {
            width: 3rem;
            height: 3rem;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
        }
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
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">Orders</a>
                </li>
                <li class="nav-item">
                    <span class="nav-link text-white-50">
                        <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <h3 class="mb-4">Welcome back, <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?> 👋</h3>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card metric-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="metric-icon" style="background-color:#6f4e37;">📦</div>
                    <div>
                        <div class="text-muted small">Total Products</div>
                        <div class="fs-4 fw-bold"><?= number_format($totalProducts) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card metric-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="metric-icon" style="background-color:#c98a3e;">🧾</div>
                    <div>
                        <div class="text-muted small">Total Orders</div>
                        <div class="fs-4 fw-bold"><?= number_format($totalOrders) ?></div>
                        <div class="text-muted small"><?= number_format($pendingOrders) ?> pending</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card metric-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="metric-icon" style="background-color:#2f7a4d;">💰</div>
                    <div>
                        <div class="text-muted small">Total Revenue</div>
                        <div class="fs-4 fw-bold">Rs. <?= number_format($totalRevenue, 2) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <a href="products.php" class="btn btn-outline-dark btn-sm me-2">Manage Products</a>
                    <a href="orders.php" class="btn btn-outline-dark btn-sm">Manage Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
