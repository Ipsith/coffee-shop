<?php
// includes/header.php — shared top navigation for every page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current = basename($_SERVER['PHP_SELF']);

// Cart Count එක නිවැරදිව ගණනය කිරීම
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item)) {
            $cartCount += intval($item['qty'] ?? $item['quantity'] ?? 1);
        } else {
            $cartCount += intval($item);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? $pageTitle . ' | Highland Roast' : 'Highland Roast Coffee'; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;0,700;1,500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ===================== NAVBAR ===================== -->
<header class="navbar" id="navbar">
  <div class="nav-container">
    <a href="index.php" class="brand">
      <span class="brand-mark">☕</span> Highland <em>Roast</em>
    </a>
    <nav class="nav-links" id="navLinks">
      <a href="index.php"   class="<?php echo $current === 'index.php'   ? 'active' : ''; ?>">Home</a>
      <a href="menu.php"    class="<?php echo $current === 'menu.php'    ? 'active' : ''; ?>">Menu</a>
      <a href="about.php"   class="<?php echo $current === 'about.php'   ? 'active' : ''; ?>">About</a>
      <a href="contact.php" class="<?php echo $current === 'contact.php' ? 'active' : ''; ?>">Contact</a>
    </nav>
    <div class="nav-actions">
      <a href="cart.php" class="cart-link">
        <i class="fa-solid fa-mug-hot"></i>
        <span class="cart-badge"><?php echo $cartCount; ?></span>
      </a>
      <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
