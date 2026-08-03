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

---

## 🇱🇰 සිංහලෙන් Setup කරන විදිය (Step by Step)

### 1️⃣ XAMPP Setup
1. XAMPP Control Panel එකෙන් **Apache** සහ **MySQL** දෙකම Start කරන්න.
2. මේ project folder එක (`coffee_shop`) සම්පූර්ණයෙන්ම copy කරලා `C:\xampp\htdocs\` folder එක ඇතුලට දාන්න.
   - උදාහරණයක් විදිහට: `C:\xampp\htdocs\coffee_shop\`

### 2️⃣ Database හදන විදිය
1. Browser එකේ `http://localhost/phpmyadmin` කියලා යන්න.
2. Top එකේ **"Import"** tab එක click කරන්න.
3. **"Choose File"** click කරලා `schema.sql` file එක select කරන්න.
4. පහළින් **"Go"** button එක click කරන්න.
5. දැන් `coffee_shop_db` කියලා database එකක්, ඒ ඇතුලේ tables 5ම (users, products, orders, order_items, chatbot_responses), සහ sample coffee items + chatbot rules auto විදිහට add වෙනවා.

> ⚠️ `config/db.php` file එකේ default XAMPP settings (`root` user, empty password) දාලා තියෙන්නේ. ඔයාගේ MySQL එකට වෙනම password එකක් තියෙනවනම් එතන update කරන්න.

### 3️⃣ Website එක Browse කරන විදිය
Browser එකේ මේ URL එකට යන්න:
```
http://localhost/coffee_shop/index.php
```
- Home page එකේ hero section එකයි, database එකෙන් dynamic ව load වෙන "Popular" coffee items 6ක් හෝ පෙන්නනවා.
- `menu.php` එකෙන් category (Hot / Cold / Specialty / Pastry) අනුව filter කරලා full menu එක බලන්න පුළුවන්.
- "Add to Cart" click කළාම, ඒක PHP `$_SESSION` එකේ save වෙනවා (login අවශ්‍ය නෑ — guest cart එකක්).
- `cart.php` එකේදී quantity වෙනස් කරන්න, items remove කරන්න, "Place Order" click කරලා checkout කරන්න පුළුවන් — ඒ order එක `orders` සහ `order_items` tables වලට save වෙනවා.

### 4️⃣ Chatbot එක Test කරන විදිය
1. පිටුවේ පහළ දකුණු කෙළවරේ (bottom-right) තියෙන **☕ floating icon** එක click කරන්න.
2. Chat window එක animate වෙලා open වෙනවා.
3. මේවා type කරලා try කරන්න:
   - `menu` → Database එකෙන් LIVE items list එකක් පෙන්නනවා
   - `hours` → Opening hours
   - `location` → Address
   - `delivery` → Delivery options
   - `I want something sweet` → Recommendation එකක්
   - Quick buttons (📋 Menu, 🕒 Hours, 🚚 Delivery, 🍯 Recommend) click කරලත් ඒම දේවල් fast ව අහන්න පුළුවන්.
4. Chatbot logic එක තියෙන්නේ `chatbot_api.php` file එකේ. `chatbot_responses` table එකට keyword-response pairs add කරලා, code එකක් නැතුවම chatbot එකට අලුත් පිළිතුරු දෙන්න පුළුවන් (phpMyAdmin එකෙන් INSERT කරන්න).

### 5️⃣ Product Images දාන විදිය (Optional)
`assets/images/` folder එකට, `schema.sql` එකේ තියෙන image නම් වලට match වෙන .jpg files දාන්න (උදා: `espresso.jpg`, `cappuccino.jpg`...). Image එකක් නැත්නම් automatic ව placeholder icon එකක් පෙන්නනවා — website එක crash වෙන්නේ නෑ.

---

## ⚙️ Requirements
- **PHP 8.0+** (uses the `str_contains()` function). Modern XAMPP installers (8.2.x) already include this — check via `php -v` in XAMPP's shell if unsure.

## 🔧 Notes for Developers
- All DB access uses **PDO with prepared statements** — protected against SQL injection.
- Cart is fully **session-based** (no login required to add items or checkout).
- The chatbot fetches **live product data from MySQL** for menu-related queries, and uses a **keyword-matched rule table** (`chatbot_responses`) for everything else — so non-technical staff can extend chatbot answers directly from phpMyAdmin.
- Animations use **CSS keyframes** (steam, cup, hover states, chat bubbles) plus **AOS (Animate On Scroll)** loaded via CDN for scroll-reveal effects.
- To add authentication (login/register), hash passwords with `password_hash()` and verify with `password_verify()` against the `users` table — the schema already has `role` (`customer`/`admin`) ready for that.
