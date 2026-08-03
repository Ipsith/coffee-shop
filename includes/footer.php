<!-- ===================== FOOTER ===================== -->
<footer class="site-footer" id="contact">
  <div class="footer-grid">
    <div class="footer-col">
      <h3><span class="brand-mark">☕</span> Highland Roast</h3>
      <p>Small-batch roasted, brewed with heart. A warm cup for every kind of day.</p>
    </div>
    <div class="footer-col">
      <h4>Visit Us</h4>
      <p><i class="fa-solid fa-location-dot"></i> No. 45, Galle Road, Colombo 03</p>
      <p><i class="fa-solid fa-clock"></i> 7:00 AM – 10:00 PM, Daily</p>
      <p><i class="fa-solid fa-phone"></i> +94 77 123 4567</p>
    </div>
    <div class="footer-col">
      <h4>Follow</h4>
      <div class="socials">
        <a href="#"><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-facebook"></i></a>
        <a href="#"><i class="fa-brands fa-tiktok"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">&copy; <?php echo date('Y'); ?> Highland Roast Coffee. All rights reserved.</div>
</footer>

<!-- ===================== CHATBOT WIDGET ===================== -->
<div class="chatbot-widget">
  <button class="chatbot-toggle" id="chatbotToggle" aria-label="Open coffee assistant">
    <i class="fa-solid fa-mug-saucer" id="chatIconOpen"></i>
    <i class="fa-solid fa-xmark" id="chatIconClose" style="display:none;"></i>
    <span class="chatbot-ping"></span>
  </button>

  <div class="chat-window" id="chatWindow">
    <div class="chat-header">
      <div class="chat-header-info">
        <div class="chat-avatar">☕</div>
        <div>
          <h4>Highland Assistant</h4>
          <span class="status-dot"></span> Online now
        </div>
      </div>
      <button id="chatMinimize" aria-label="Minimize"><i class="fa-solid fa-minus"></i></button>
    </div>

    <div class="chat-body" id="chatBody">
      <!-- messages injected by chatbot.js -->
    </div>

    <div class="chat-quick-options" id="chatQuickOptions">
      <button class="quick-btn" data-msg="Show me the menu">📋 Menu</button>
      <button class="quick-btn" data-msg="What are your opening hours?">🕒 Hours</button>
      <button class="quick-btn" data-msg="Do you deliver?">🚚 Delivery</button>
      <button class="quick-btn" data-msg="Recommend something sweet">🍯 Recommend</button>
    </div>

    <form class="chat-input-area" id="chatForm">
      <input type="text" id="chatInput" placeholder="Ask about coffee, menu, hours..." autocomplete="off" required>
      <button type="submit" aria-label="Send"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/chatbot.js"></script>
</body>
</html>
