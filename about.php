<?php
require_once 'config/db.php';
$pageTitle = 'About Us';

// Pull a couple of real numbers from the DB so the stats aren't hardcoded fiction
$productCount = (int) $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orderCount   = (int) $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();

include 'includes/header.php';
?>

<!-- ===================== ABOUT HERO ===================== -->
<section class="page-hero" data-aos="fade-up">
  <p class="section-eyebrow">Our Story</p>
  <h1>Coffee, brewed with a<br><span class="text-gold">Highland heart</span></h1>
  <p class="page-hero-sub">From a single cart on Galle Road to a home for coffee lovers across Colombo — here's how we got here.</p>
</section>

<!-- ===================== STORY SPLIT ===================== -->
<section class="section about">
  <div class="about-image" data-aos="fade-right">
    <div class="about-frame"></div>
  </div>
  <div class="about-text" data-aos="fade-left">
    <p class="section-eyebrow">Where it began</p>
    <h2>Roasted with patience, poured with pride</h2>
    <p>Highland Roast started in 2018 as a single espresso cart run by two friends who couldn't find a "proper" cup of coffee in the city. What began as weekend roasting experiments in a home kitchen grew into a full-batch roastery and, eventually, this shop.</p>
    <p>We still roast in small batches, source directly from Sri Lankan and international estates, and refuse to pre-grind our beans until the moment your order hits the machine. Every cup is brewed to order — never rushed, never reheated.</p>
    <ul class="about-stats">
      <li><strong><?php echo max($productCount, 12); ?>+</strong><span>Menu Items</span></li>
      <li><strong>50k+</strong><span>Cups Served</span></li>
      <li><strong>4.9★</strong><span>Average Rating</span></li>
    </ul>
  </div>
</section>

<!-- ===================== VALUES ===================== -->
<section class="section">
  <div class="section-head" data-aos="fade-up">
    <p class="section-eyebrow">What We Stand For</p>
    <h2>Our promise, in every cup</h2>
  </div>
  <div class="values-grid">
    <div class="value-card" data-aos="fade-up">
      <div class="value-icon"><i class="fa-solid fa-seedling"></i></div>
      <h3>Direct-Trade Beans</h3>
      <p>We work directly with growers in Sri Lanka's hill country and beyond, paying fair prices for exceptional beans.</p>
    </div>
    <div class="value-card" data-aos="fade-up" data-aos-delay="100">
      <div class="value-icon"><i class="fa-solid fa-fire-flame-curved"></i></div>
      <h3>Small-Batch Roasting</h3>
      <p>Every batch is roasted in-house, in small quantities, so flavor never sits on a shelf for months before it reaches you.</p>
    </div>
    <div class="value-card" data-aos="fade-up" data-aos-delay="200">
      <div class="value-icon"><i class="fa-solid fa-heart"></i></div>
      <h3>Made For You</h3>
      <p>No two orders are identical — our baristas and our chatbot alike are here to help you find your perfect cup.</p>
    </div>
  </div>
</section>

<!-- ===================== TIMELINE ===================== -->
<section class="section timeline-section">
  <div class="section-head" data-aos="fade-up">
    <p class="section-eyebrow">Milestones</p>
    <h2>Our journey so far</h2>
  </div>
  <div class="timeline">
    <div class="timeline-item" data-aos="fade-up">
      <span class="timeline-year">2018</span>
      <p>Two friends, one espresso cart, and a folding table on Galle Road.</p>
    </div>
    <div class="timeline-item" data-aos="fade-up" data-aos-delay="100">
      <span class="timeline-year">2020</span>
      <p>Opened our first roastery and began direct-trade partnerships with local estates.</p>
    </div>
    <div class="timeline-item" data-aos="fade-up" data-aos-delay="200">
      <span class="timeline-year">2023</span>
      <p>Opened the Highland Roast flagship store — the one you're browsing right now.</p>
    </div>
    <div class="timeline-item" data-aos="fade-up" data-aos-delay="300">
      <span class="timeline-year">Today</span>
      <p>Serving <?php echo $orderCount > 0 ? number_format($orderCount) : 'thousands of'; ?> orders and counting, with our Highland Assistant chatbot ready to help around the clock.</p>
    </div>
  </div>
</section>

<!-- ===================== TEAM ===================== -->
<section class="section">
  <div class="section-head" data-aos="fade-up">
    <p class="section-eyebrow">Behind the Counter</p>
    <h2>Meet the people pouring your coffee</h2>
  </div>
  <div class="team-grid">
    <div class="team-card" data-aos="fade-up">
      <div class="team-avatar">👩‍🍳</div>
      <h4>Ishara Perera</h4>
      <span>Founder & Head Roaster</span>
    </div>
    <div class="team-card" data-aos="fade-up" data-aos-delay="100">
      <div class="team-avatar">👨‍🍳</div>
      <h4>Kasun Fernando</h4>
      <span>Co-Founder & Barista Trainer</span>
    </div>
    <div class="team-card" data-aos="fade-up" data-aos-delay="200">
      <div class="team-avatar">👩‍💼</div>
      <h4>Nadeesha Silva</h4>
      <span>Store Manager</span>
    </div>
    <div class="team-card" data-aos="fade-up" data-aos-delay="300">
      <div class="team-avatar">🧑‍🍳</div>
      <h4>Tharindu Jayasuriya</h4>
      <span>Lead Barista</span>
    </div>
  </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="section about-cta" data-aos="fade-up">
  <h2>Ready for your next cup?</h2>
  <p>Browse the full menu or drop by — we'd love to brew one for you.</p>
  <div class="hero-actions" style="justify-content:center;">
    <a href="menu.php" class="btn btn-primary">Explore Menu <i class="fa-solid fa-arrow-right"></i></a>
    <a href="contact.php" class="btn btn-ghost">Get in Touch</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
