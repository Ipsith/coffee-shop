<?php
/**
 * admin/products.php
 * ---------------------------------------------------------
 * Full CRUD for the `products` table (id, name, price, category,
 * description). Add / Edit share one form; Delete is a POST-only
 * action guarded by a CSRF token.
 * ---------------------------------------------------------
 */

require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Administrator';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$allowedCategories = ['hot', 'cold', 'specialty', 'pastry'];
$message = '';
$messageType = 'success';
$editProduct = null; // product row being edited, if any

function csrf_ok(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------------------------------------------------
// Handle POST actions: create, update, delete
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!csrf_ok($_POST['csrf_token'] ?? '')) {
        $message = 'Your session expired, please try again.';
        $messageType = 'danger';
    } else {
        try {
            if ($action === 'create' || $action === 'update') {
                $name        = trim($_POST['name'] ?? '');
                $price       = (float) ($_POST['price'] ?? 0);
                $category    = $_POST['category'] ?? 'hot';
                $description = trim($_POST['description'] ?? '');

                if ($name === '' || $price < 0 || !in_array($category, $allowedCategories, true)) {
                    $message = 'Please provide a valid name, price, and category.';
                    $messageType = 'danger';
                } elseif ($action === 'create') {
                    $stmt = $pdo->prepare(
                        "INSERT INTO products (name, price, category, description) 
                         VALUES (:name, :price, :category, :description)"
                    );
                    $stmt->execute([
                        'name'        => $name,
                        'price'       => $price,
                        'category'    => $category,
                        'description' => $description,
                    ]);
                    $message = 'Product added successfully.';
                } else { // update
                    $id = (int) ($_POST['id'] ?? 0);
                    $stmt = $pdo->prepare(
                        "UPDATE products 
                         SET name = :name, price = :price, category = :category, description = :description 
                         WHERE id = :id"
                    );
                    $stmt->execute([
                        'name'        => $name,
                        'price'       => $price,
                        'category'    => $category,
                        'description' => $description,
                        'id'          => $id,
                    ]);
                    $message = 'Product updated successfully.';
                }
            } elseif ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $message = 'Product deleted.';
            }
        } catch (PDOException $e) {
            error_log('Products CRUD error: ' . $e->getMessage());
            $message = 'A database error occurred. Please try again.';
            $messageType = 'danger';
        }
    }
}

// ---------------------------------------------------------
// Load product to edit (GET ?edit=ID)
// ---------------------------------------------------------
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $editId]);
        $editProduct = $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Products fetch-for-edit error: ' . $e->getMessage());
    }
}

// ---------------------------------------------------------
// Fetch all products for the listing table
// ---------------------------------------------------------
try {
    $products = $conn->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    error_log('Products list error: ' . $e->getMessage());
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f1ee; }
        .navbar-brew { background-color: #4a2c2a; }
        .navbar-brew .nav-link { color: rgba(255,255,255,.85); }
        .navbar-brew .nav-link:hover, .navbar-brew .nav-link.active { color: #fff; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 .25rem .75rem rgba(0,0,0,.08); }
        .btn-brew { background-color: #6f4e37; border-color: #6f4e37; color: #fff; }
        .btn-brew:hover { background-color: #4a2c2a; border-color: #4a2c2a; color: #fff; }
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
                <li class="nav-item"><a class="nav-link active" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                <li class="nav-item">
                    <span class="nav-link text-white-50"><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></span>
                </li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <h3 class="mb-4">Manage Products</h3>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Add / Edit form -->
    <div class="card p-4 mb-4">
        <h5 class="mb-3"><?= $editProduct ? 'Edit Product #' . (int) $editProduct['id'] : 'Add New Product' ?></h5>
        <form method="POST" action="products.php" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="<?= $editProduct ? 'update' : 'create' ?>">
            <?php if ($editProduct): ?>
                <input type="hidden" name="id" value="<?= (int) $editProduct['id'] ?>">
            <?php endif; ?>

            <div class="col-md-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required
                       value="<?= htmlspecialchars($editProduct['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Price (Rs.)</label>
                <input type="number" step="0.01" min="0" name="price" class="form-control" required
                       value="<?= htmlspecialchars((string) ($editProduct['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <?php foreach ($allowedCategories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"
                            <?= (isset($editProduct['category']) && $editProduct['category'] === $cat) ? 'selected' : '' ?>>
                            <?= ucfirst($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-brew flex-fill">
                    <?= $editProduct ? 'Update' : 'Add' ?> Product
                </button>
                <?php if ($editProduct): ?>
                    <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editProduct['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </form>
    </div>

    <!-- Products listing -->
    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No products found.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= (int) $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($p['category']), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>Rs. <?= number_format((float) $p['price'], 2) ?></td>
                            <td class="text-truncate" style="max-width: 260px;">
                                <?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="text-end">
                                <a href="products.php?edit=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                <form method="POST" action="products.php" class="d-inline"
                                      onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
