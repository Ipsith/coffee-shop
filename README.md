# Highland Roast — Coffee Shop Website (XAMPP / PHP / MySQL)

## 📁 Project Structure
```
coffee_shop/
├── schema.sql                 → Database import file (phpMyAdmin)
├── config/db.php              → PDO database connection
├── index.php                  → Home page (hero + dynamic popular menu)
├── menu.php                   → Full menu, filtered by category
├── cart.php                   → Cart system (PHP sessions) + checkout
├── chatbot_api.php            → AJAX chatbot endpoint
├── includes/
│   ├── header.php             → Shared navbar
│   └── footer.php             → Shared footer + chatbot widget HTML
└── assets/
    ├── css/style.css          → All styling & animations
    ├── js/main.js             → Nav, AOS init, cart qty buttons
    ├── js/chatbot.js          → Chatbot AJAX + UI logic
    └── images/                → Product photos (add your own .jpg files here)
```


