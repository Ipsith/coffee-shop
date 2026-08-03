<?php
/**
 * cart.php
 */
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Item එක Cart එකට එකතු කිරීම / Update කිරීම / Remove කිරීම
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);
    $qty = intval($_POST['quantity'] ?? 1);

    if ($product_id > 0) {
        if ($action === 'add' || $action === 'update') {
            if ($qty > 0) {
                // Cart එකේ තිබුණොත් quantity එකතු වේ, නැතහොත් අලුතින් add වේ
                if (isset($_SESSION['cart'][$product_id]) && $action === 'add') {
                    $_SESSION['cart'][$product_id] += $qty;
                } else {
                    $_SESSION['cart'][$product_id] = $qty;
                }
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    
    header('Location: cart.php');
    exit;
}

// Fetch Cart Products from Database
$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $product_ids = array_filter($product_ids, 'is_numeric');

    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute(array_values($product_ids));
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $pid = $product['id'];
            $cart_item = $_SESSION['cart'][$pid];
            $quantity = is_array($cart_item) ? intval($cart_item['qty'] ?? 1) : intval($cart_item);
            $price = floatval($product['price']);
            
            $subtotal = $price * $quantity;
            $total_price += $subtotal;

            $cart_items[] = [
                'id'       => $product['id'],
                'name'     => $product['name'],
                'price'    => $price,
                'image'    => $product['image'] ?? 'default-coffee.svg',
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5 my-4" style="background-color: #ffffff; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
    <h2 class="mb-4 text-center fw-bold" style="color: #4a2c2a;">🛒 Your Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5">
            <p class="fs-4 fw-bold text-dark mb-4">Your cart is currently empty! ☕</p>
            <a href="menu.php" class="btn btn-lg text-white shadow-sm" style="background-color: #6f4e37; border-radius: 8px;">Explore Menu ☕</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-dark">
                                <thead style="background-color: #f8f5f0; color: #4a2c2a;">
                                    <tr>
                                        <th style="padding-left: 20px;">Item</th>
                                        <th>Price</th>
                                        <th style="width: 120px;">Quantity</th>
                                        <th>Subtotal</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td style="padding-left: 20px;">
                                                <div class="d-flex align-items-center">
                                                    <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                                         onerror="this.onerror=null;this.src='assets/images/default-coffee.svg'" 
                                                         alt="Coffee" style="width: 55px; height: 55px; object-fit: cover;" class="rounded me-3 border">
                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($item['name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-dark">Rs. <?php echo number_format($item['price'], 2); ?></td>
                                            <td>
                                                <form action="cart.php" method="POST">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control form-control-sm text-center" onchange="this.form.submit()">
                                                </form>
                                            </td>
                                            <td class="fw-bold" style="color: #6f4e37;">Rs. <?php echo number_format($item['subtotal'], 2); ?></td>
                                            <td class="text-center">
                                                <form action="cart.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="remove">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 rounded-3" style="background-color: #fcf9f5;">
                    <h4 class="card-title mb-3 fw-bold" style="color: #4a2c2a;">Order Summary</h4>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 mb-3 text-dark">
                        <span>Total Amount:</span>
                        <strong style="color: #6f4e37;">Rs. <?php echo number_format($total_price, 2); ?></strong>
                    </div>
                    <a href="checkout.php" class="btn w-100 py-2 fs-5 text-white fw-bold shadow-sm" style="background-color: #6f4e37; border-radius: 8px;">
                        Proceed to Checkout ☕
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
