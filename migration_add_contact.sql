-- =========================================================
-- migration_add_contact.sql
-- Run this ONLY if you already imported the old schema.sql
-- and don't want to re-import everything from scratch.
-- In phpMyAdmin: select coffee_shop_db → SQL tab → paste → Go
-- =========================================================

USE coffee_shop_db;

CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    subject     VARCHAR(180) DEFAULT NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
