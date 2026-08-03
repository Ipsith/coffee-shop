<?php
require_once 'config/db.php';
$pageTitle = 'Home';

// Fetch popular items for the homepage preview (dynamic from MySQL)
$stmt = $conn->query("SELECT * FROM products WHERE is_popular = 1 ORDER BY created_at DESC LIMIT 6");
$popularItems = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-steam">
    <span></span><span></span><span></span>
  </div>
  <div class="hero-content" data-aos="fade-up">
    <p class="hero-eyebrow">Small batch &middot; Slow roasted &middot; Colombo</p>
    <h1>Every cup tells<br><span class="text-gold">a warmer story</span></h1>
    <p class="hero-sub">Highland Roast blends single-origin beans with old-world craft to pour a cup that feels like coming home.</p>
    <div class="hero-actions">
      <a href="menu.php" class="btn btn-primary">Explore Menu <i class="fa-solid fa-arrow-right"></i></a>
      <a href="#popular" class="btn btn-ghost">See Bestsellers</a>
    </div>
  </div>
  <div class="hero-cup" data-aos="fade-left" data-aos-delay="200">
    <div class="cup-body">
      <div class="cup-liquid"></div>
      <div class="cup-shine"></div>
    </div>
    <div class="cup-handle"></div>
    <div class="cup-saucer"></div>
  </div>
</section>

<!-- ===================== POPULAR PICKS ===================== -->
<section class="section popular" id="popular">
  <div class="section-head" data-aos="fade-up">
    <p class="section-eyebrow">Bestsellers</p>
    <h2>Loved by our regulars</h2>
  </div>

  <div class="menu-grid">
    <?php foreach ($popularItems as $i => $item): ?>
    <div class="coffee-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 80; ?>">
      <div class="card-image">
        <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>"
             onerror="this.onerror=null;this.src='assets/images/default-coffee.svg'"
             alt="<?php echo htmlspecialchars($item['name']); ?>">
        <span class="card-tag">Popular</span>
      </div>
      <div class="card-body">
        <div class="card-top">
          <h3><?php echo htmlspecialchars($item['name']); ?></h3>
          <span class="price">Rs. <?php echo number_format($item['price'], 2); ?></span>
        </div>
        <p><?php echo htmlspecialchars($item['description']); ?></p>
        <form action="cart.php" method="POST" class="card-actions">
          <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
          <input type="hidden" name="action" value="add">
          <button type="submit" class="btn btn-small">Add to Cart <i class="fa-solid fa-cart-plus"></i></button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ===================== ABOUT ===================== -->
<section class="section about" id="about">
  <div class="about-image" data-aos="fade-right">
    <div class="about-frame"></div>
  </div>
  <div class="about-text" data-aos="fade-left">
    <p class="section-eyebrow">Our Story</p>
    <h2>Roasted with patience, poured with pride</h2>
    <p>Since our first cart on Galle Road, Highland Roast has grown one honest cup at a time. We source directly from Sri Lankan and international estates, roast in small batches, and brew every order fresh — never rushed.</p>
    <ul class="about-stats">
      <li><strong>12+</strong><span>Signature Blends</span></li>
      <li><strong>50k+</strong><span>Cups Served</span></li>
      <li><strong>4.9★</strong><span>Average Rating</span></li>
    </ul>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
