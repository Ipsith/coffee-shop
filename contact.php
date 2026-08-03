<?php
require_once 'config/db.php';
$pageTitle = 'Contact Us';

$errors = [];
$success = false;

// ---------------------------------------------------------
// Handle contact form submission
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') $errors[] = 'Please enter your name.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($message === '') $errors[] = 'Please write a message before sending.';

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)"
        );
        $stmt->execute([
            'name'    => $name,
            'email'   => $email,
            'subject' => $subject !== '' ? $subject : 'General Inquiry',
            'message' => $message,
        ]);

        // Redirect (POST/Redirect/GET pattern) to avoid resubmission on refresh
        header('Location: contact.php?sent=1');
        exit;
    }
}

if (isset($_GET['sent'])) {
    $success = true;
}

include 'includes/header.php';
?>

<section class="page-hero" data-aos="fade-up">
  <p class="section-eyebrow">Get In Touch</p>
  <h1>We'd love to<br><span class="text-gold">hear from you</span></h1>
  <p class="page-hero-sub">Questions, feedback, catering requests, or just want to say hello? Send us a message below.</p>
</section>

<section class="section contact-section">
  <div class="contact-layout">

    <!-- ---------- Contact form ---------- -->
    <div class="contact-form-card" data-aos="fade-up">
      <h3>Send a Message</h3>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="fa-solid fa-circle-check"></i>
          Thanks for reaching out! We've received your message and will reply within 24 hours.
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <div>
            <?php foreach ($errors as $err): ?>
              <div><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <form action="contact.php" method="POST" class="contact-form">
        <input type="hidden" name="action" value="send_message">
        <div class="form-row">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Your name" required
                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" required
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" placeholder="What's this about?"
                 value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="message">Message</label>
          <textarea id="message" name="message" rows="5" placeholder="Type your message here..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Send Message <i class="fa-solid fa-paper-plane"></i></button>
      </form>
    </div>

    <!-- ---------- Contact info ---------- -->
    <div class="contact-info" data-aos="fade-up" data-aos-delay="150">
      <div class="info-card">
        <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
        <div>
          <h4>Visit Us</h4>
          <p>No. 530/85, Stage 01, Anuradhapura, Sri Lanka</p>
        </div>
      </div>
      <div class="info-card">
        <div class="info-icon"><i class="fa-solid fa-clock"></i></div>
        <div>
          <h4>Opening Hours</h4>
          <p>Every day, 7:00 AM – 10:00 PM</p>
        </div>
      </div>
      <div class="info-card">
        <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
        <div>
          <h4>Call Us</h4>
          <p>+94 76 168 8074</p>
        </div>
      </div>
      <div class="info-card">
        <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
        <div>
          <h4>Email</h4>
          <p>hello@highlandroast.com</p>
        </div>
      </div>
      <div class="socials contact-socials">
        <a href="#"><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-facebook"></i></a>
        <a href="#"><i class="fa-brands fa-tiktok"></i></a>
      </div>
    </div>
  </div>

  <!-- ---------- Map ---------- -->
  <div class="map-embed" data-aos="fade-up">
    <iframe
      src="https://maps.google.com/maps?q=Colombo%2003%2C%20Sri%20Lanka&t=&z=14&ie=UTF8&iwloc=&output=embed"
      width="100%" height="320" style="border:0;" allowfullscreen loading="lazy"
      referrerpolicy="no-referrer-when-downgrade" title="Highland Roast location map">
    </iframe>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
