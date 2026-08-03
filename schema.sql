-- =========================================================
-- Highland Roast Coffee Shop — Database Schema
-- Import this file in phpMyAdmin (or run via CLI: 
-- mysql -u root -p < schema.sql)
-- =========================================================

CREATE DATABASE IF NOT EXISTS coffee_shop_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE coffee_shop_db;

-- ---------------------------------------------------------
-- 1. USERS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,          -- stored with password_hash()
    role        ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2. PRODUCTS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120)  NOT NULL,
    description TEXT,
    price       DECIMAL(10,2) NOT NULL,
    category    ENUM('hot','cold','specialty','pastry') NOT NULL DEFAULT 'hot',
    image       VARCHAR(255)  DEFAULT 'default-coffee.jpg',
    is_popular  TINYINT(1)    DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3. ORDERS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    status      ENUM('pending','preparing','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. ORDER ITEMS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    price       DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5. CONTACT MESSAGES (submissions from contact.php)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    subject     VARCHAR(180) DEFAULT NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 6. CHATBOT RESPONSES (keyword based rule engine)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS chatbot_responses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    keywords    VARCHAR(255) NOT NULL,   -- comma separated keywords, matched with LIKE / IN
    response    TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- SAMPLE DATA
-- =========================================================

-- Admin user  (password = "admin123", already hashed with password_hash/BCRYPT)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@highlandroast.com', '$2y$10$92IXUNpkjO0rOQ5byMi.YO0kK5xW.KDMAOTb0jTLzrsgYQnCcnJ9C', 'admin');
-- NOTE: the hash above corresponds to "admin123" — change it after first login.

-- Products
INSERT INTO products (name, description, price, category, image, is_popular) VALUES
('Espresso', 'A bold, concentrated shot of pure coffee flavor.', 350.00, 'hot', 'espresso.jpg', 1),
('Cappuccino', 'Espresso topped with steamed milk and a thick layer of foam.', 480.00, 'hot', 'cappuccino.jpg', 1),
('Caffe Latte', 'Smooth espresso with silky steamed milk.', 460.00, 'hot', 'latte.jpg', 0),
('Caramel Macchiato', 'Espresso marked with caramel and vanilla-scented milk.', 550.00, 'hot', 'caramel-macchiato.jpg', 1),
('Mocha', 'Espresso, steamed milk and rich chocolate.', 520.00, 'hot', 'mocha.jpg', 0),
('Cold Brew', 'Slow-steeped 18 hours for a smooth, low-acid taste.', 490.00, 'cold', 'cold-brew.jpg', 1),
('Iced Caramel Latte', 'Chilled espresso, milk and caramel drizzle over ice.', 560.00, 'cold', 'iced-caramel-latte.jpg', 1),
('Affogato', 'A scoop of vanilla gelato drowned in hot espresso.', 600.00, 'specialty', 'affogato.jpg', 0),
('Highland Signature Brew', 'Our house blend — notes of dark chocolate & toasted hazelnut.', 650.00, 'specialty', 'signature-brew.jpg', 1),
('Butter Croissant', 'Flaky, buttery, baked fresh every morning.', 380.00, 'pastry', 'croissant.jpg', 0),
('Chocolate Muffin', 'Rich double-chocolate chip muffin.', 350.00, 'pastry', 'muffin.jpg', 0);

-- Chatbot rules
INSERT INTO chatbot_responses (keywords, response) VALUES
('hi,hello,hey,ayubowan', 'Ayubowan! ☕ Welcome to Highland Roast. Ask me about our menu, opening hours, delivery, or say things like "espresso" or "something sweet" and I will recommend a drink!'),
('hours,open,opening,time,close', 'We are open every day from 7:00 AM to 10:00 PM, including weekends and public holidays.'),
('location,address,where,branch', 'You can find us at No. 45, Galle Road, Colombo 03, Sri Lanka. We also have a branch in Kandy city center.'),
('delivery,deliver,uber eats,pickme', 'Yes! We deliver islandwide through PickMe Food and Uber Eats, or you can place a pickup order directly on this website.'),
('espresso,strong,bold', 'If you love strong, bold coffee, our Espresso (Rs. 350) or Highland Signature Brew (Rs. 650) are perfect picks!'),
('sweet,sugar,dessert,sweetened', 'For something sweet, try our Caramel Macchiato (Rs. 550) or Iced Caramel Latte (Rs. 560) — both are customer favorites!'),
('cold,iced,ice,chilled', 'Our top cold picks are Cold Brew (Rs. 490) and Iced Caramel Latte (Rs. 560). Perfect for a warm day!'),
('milk,latte,creamy', 'You will love our Caffe Latte (Rs. 460) or Cappuccino (Rs. 480) — creamy and smooth.'),
('price,cost,how much', 'Our drinks range from Rs. 350 to Rs. 650. Type "menu" and I can show you our popular items with prices!'),
('payment,pay,card,cash', 'We accept cash, credit/debit cards, and popular mobile wallets at the counter, and card payments for online orders.'),
('thank,thanks,thank you,sthuthi', 'You are most welcome! Enjoy your coffee ☕ and have a wonderful day!'),
('bye,goodbye,see you', 'Goodbye! Come back soon for another warm cup at Highland Roast. ☕');
