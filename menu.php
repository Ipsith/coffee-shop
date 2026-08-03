<?php
require_once 'config/db.php';
$pageTitle = 'Menu';

// Handle category filter (?category=hot|cold|specialty|pastry|all)
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$validCategories = ['hot', 'cold', 'specialty', 'pastry'];

if (in_array($category, $validCategories, true)) {
    // $pdo වෙනුවට $conn භාවිතා කර සකස් කරන ලදී
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = :cat ORDER BY name ASC");
    $stmt->execute(['cat' => $category]);
} else {
    $category = 'all';
    $stmt = $conn->query("SELECT * FROM products ORDER BY category, name ASC");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<section class="page-hero" data-aos="fade-up">
  <p class="section-eyebrow">Full Menu</p>
  <h1>Brewed for every mood</h1>
</section>

<section class="section">
  <!-- Category filter tabs -->
  <div class="filter-tabs" data-aos="fade-up">
    <a href="menu.php?category=all"       class="tab <?php echo $category === 'all' ? 'active' : ''; ?>">All</a>
    <a href="menu.php?category=hot"       class="tab <?php echo $category === 'hot' ? 'active' : ''; ?>">Hot Coffee</a>
    <a href="menu.php?category=cold"      class="tab <?php echo $category === 'cold' ? 'active' : ''; ?>">Cold Coffee</a>
    <a href="menu.php?category=specialty" class="tab <?php echo $category === 'specialty' ? 'active' : ''; ?>">Specialty</a>
    <a href="menu.php?category=pastry"    class="tab <?php echo $category === 'pastry' ? 'active' : ''; ?>">Pastries</a>
  </div>

  <?php if (empty($products)): ?>
    <p class="empty-state" style="text-align: center; padding: 40px; color: #666;">No items found in this category yet — check back soon!</p>
  <?php endif; ?>

  <div class="menu-grid">
    <?php foreach ($products as $i => $item): ?>
    <div class="coffee-card" data-aos="fade-up" data-aos-delay="<?php echo ($i % 6) * 80; ?>">
      <div class="card-image">
        <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>"
             onerror="this.onerror=null;this.src='assets/images/default-coffee.svg'"
             alt="<?php echo htmlspecialchars($item['name']); ?>">
        <?php if (!empty($item['is_popular'])): ?><span class="card-tag">Popular</span><?php endif; ?>
      </div>
      <div class="card-body">
        <div class="card-top">
          <h3><?php echo htmlspecialchars($item['name']); ?></h3>
          <span class="price">Rs. <?php echo number_format($item['price'], 2); ?></span>
        </div>
        <p><?php echo htmlspecialchars($item['description']); ?></p>
        
        <!-- Cart Form -->
        <form action="cart.php" method="POST" class="card-actions">
          <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="quantity" value="1">
          <button type="submit" class="btn btn-small">Add to Cart <i class="fa-solid fa-cart-plus"></i></button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
