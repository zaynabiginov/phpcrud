-- База данных для учёта отпуска на склад и продаж хозяйственного магазина
CREATE DATABASE IF NOT EXISTS magazine CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE magazine;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    cost_unit DECIMAL(12,2) NOT NULL DEFAULT 0,
    markup_pct DECIMAL(6,2) NOT NULL DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('income','expense') NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    amount DECIMAL(12,2) NOT NULL,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Миграции для существующей базы (db.php применяет автоматически):
-- ALTER TABLE transactions ADD COLUMN quantity INT NOT NULL DEFAULT 1 AFTER product_id;
-- ALTER TABLE products ADD COLUMN description TEXT NULL AFTER name;
-- ALTER TABLE products MODIFY COLUMN category_id INT NULL DEFAULT NULL; (и DROP FOREIGN KEY ...)
-- DROP TABLE categories;  -- после удаления зависимостей

-- Примеры товаров
INSERT INTO products (name, description, cost_unit, markup_pct) VALUES
('Смеситель для раковины', NULL, 98000, 100),
('Унитаз', NULL, 890000, 140),
('Душевая кабина', NULL, 2450000, 100),
('Выключатель', NULL, 9000, 140),
('Розетка', NULL, 9500, 100),
('Автоматический выключатель', NULL, 32000, 140),
('Дрель', NULL, 620000, 100),
('Отвёртка', NULL, 8500, 140),
('Молоток', NULL, 18000, 100),
('Краска акриловая', NULL, 78000, 140),
('Лак', NULL, 56000, 100),
('Саморез', NULL, 900, 140),
('Дюбель', NULL, 850, 100),
('Моющее средство', NULL, 9500, 140),
('Губка', NULL, 1800, 100),
('Семена', NULL, 4800, 140),
('Удобрение', NULL, 19000, 100);
