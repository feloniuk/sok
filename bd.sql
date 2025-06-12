-- Создание базы данных
CREATE DATABASE IF NOT EXISTS juice_sales_db;
USE juice_sales_db;

-- Таблица пользователей (для всех ролей)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'sales_manager', 'warehouse_manager', 'customer') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица категорий продуктов
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица продуктов
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Таблица складов
CREATE TABLE warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    manager_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Таблица складских записей
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE
);

-- Таблица заказов
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('credit_card', 'bank_transfer', 'cash_on_delivery') NOT NULL,
    shipping_address TEXT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица товаров в заказе
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Таблица для записей о движении товаров
CREATE TABLE inventory_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    quantity INT NOT NULL, -- положительное для прихода, отрицательное для расхода
    movement_type ENUM('incoming', 'outgoing', 'adjustment') NOT NULL,
    reference_id INT, -- может быть id заказа или другого документа
    reference_type VARCHAR(50), -- тип ссылки (order, transfer, etc.)
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица для скидок и акций
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed_amount') NOT NULL,
    discount_value DECIMAL(10, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица для связи акций с продуктами
CREATE TABLE promotion_products (
    promotion_id INT NOT NULL,
    product_id INT NOT NULL,
    PRIMARY KEY (promotion_id, product_id),
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Таблица для аналитики продаж
CREATE TABLE sales_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    product_id INT NOT NULL,
    quantity_sold INT NOT NULL DEFAULT 0,
    revenue DECIMAL(10, 2) NOT NULL DEFAULT 0,
    cost DECIMAL(10, 2) NOT NULL DEFAULT 0,
    profit DECIMAL(10, 2) NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

USE juice_sales_db;

-- Вставка тестовых пользователей
INSERT INTO users (username, email, password, role, first_name, last_name, phone) VALUES
-- Пароль для всех пользователей: 'password123' (хеш для PHP password_hash)
('admin', 'admin@juicesales.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'User', '+380501234567'),
('sales1', 'sales1@juicesales.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sales_manager', 'Sales', 'Manager', '+380502345678'),
('warehouse1', 'warehouse1@juicesales.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warehouse_manager', 'Warehouse', 'Manager', '+380503456789'),
('customer1', 'customer1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'John', 'Doe', '+380504567890'),
('customer2', 'customer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Jane', 'Smith', '+380505678901'),
('customer3', 'customer3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Robert', 'Johnson', '+380506789012'),
('customer4', 'customer4@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Emily', 'Williams', '+380507890123'),
('customer5', 'customer5@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Michael', 'Brown', '+380508901234');

-- Вставка категорий продуктов
INSERT INTO categories (name, description, image) VALUES
('Фруктовые соки', 'Натуральные соки из свежих фруктов', 'fruit_juices.jpg'),
('Овощные соки', 'Полезные соки из свежих овощей', 'vegetable_juices.jpg'),
('Смузи', 'Густые напитки из смеси фруктов и овощей', 'smoothies.jpg'),
('Детокс напитки', 'Очищающие соки для детоксикации организма', 'detox_drinks.jpg'),
('Органические соки', 'Соки из органических фруктов и овощей', 'organic_juices.jpg');

-- Вставка продуктов
INSERT INTO products (category_id, name, description, price, stock_quantity, image, is_featured, is_active) VALUES
(1, 'Апельсиновый сок', 'Свежевыжатый сок из спелых апельсинов', 89.99, 100, 'orange_juice.jpg', TRUE, TRUE),
(1, 'Яблочный сок', 'Натуральный сок из сочных яблок', 79.99, 120, 'apple_juice.jpg', FALSE, TRUE),
(1, 'Ананасовый сок', 'Экзотический сок из свежих ананасов', 99.99, 80, 'pineapple_juice.jpg', TRUE, TRUE),
(2, 'Морковный сок', 'Полезный сок из свежей моркови', 69.99, 90, 'carrot_juice.jpg', FALSE, TRUE),
(2, 'Томатный сок', 'Классический сок из спелых томатов', 59.99, 110, 'tomato_juice.jpg', FALSE, TRUE),
(2, 'Свекольный сок', 'Полезный сок из свежей свеклы', 69.99, 70, 'beet_juice.jpg', FALSE, TRUE),
(3, 'Банановый смузи', 'Густой смузи из бананов и молока', 109.99, 60, 'banana_smoothie.jpg', TRUE, TRUE),
(3, 'Ягодный смузи', 'Вкусный смузи из свежих ягод', 119.99, 50, 'berry_smoothie.jpg', TRUE, TRUE),
(4, 'Зеленый детокс', 'Очищающий напиток из зелени и овощей', 129.99, 40, 'green_detox.jpg', FALSE, TRUE),
(4, 'Цитрусовый детокс', 'Детокс-напиток из цитрусовых и имбиря', 139.99, 45, 'citrus_detox.jpg', TRUE, TRUE),
(5, 'Органический яблочный сок', 'Сок из органических яблок', 129.99, 55, 'organic_apple_juice.jpg', FALSE, TRUE),
(5, 'Органический гранатовый сок', 'Сок из органических гранатов', 149.99, 35, 'organic_pomegranate_juice.jpg', TRUE, TRUE);

-- Вставка складов
INSERT INTO warehouses (name, address, manager_id) VALUES
('Основной склад', 'ул. Складская, 1, Киев', 3),
('Северный склад', 'ул. Северная, 10, Харьков', 3),
('Южный склад', 'ул. Приморская, 25, Одесса', NULL);

-- Вставка данных инвентаря
INSERT INTO inventory (product_id, warehouse_id, quantity) VALUES
(1, 1, 50), (1, 2, 30), (1, 3, 20),
(2, 1, 60), (2, 2, 40), (2, 3, 20),
(3, 1, 40), (3, 2, 25), (3, 3, 15),
(4, 1, 45), (4, 2, 30), (4, 3, 15),
(5, 1, 55), (5, 2, 35), (5, 3, 20),
(6, 1, 30), (6, 2, 25), (6, 3, 15),
(7, 1, 30), (7, 2, 20), (7, 3, 10),
(8, 1, 25), (8, 2, 15), (8, 3, 10),
(9, 1, 20), (9, 2, 10), (9, 3, 10),
(10, 1, 25), (10, 2, 10), (10, 3, 10),
(11, 1, 30), (11, 2, 15), (11, 3, 10),
(12, 1, 20), (12, 2, 10), (12, 3, 5);

-- Вставка заказов
INSERT INTO orders (customer_id, order_number, status, total_amount, payment_method, shipping_address, notes) VALUES
(4, 'ORD-001', 'delivered', 539.94, 'credit_card', 'ул. Шевченко, 10, кв. 5, Киев', 'Доставить до 18:00'),
(5, 'ORD-002', 'shipped', 359.96, 'bank_transfer', 'ул. Франко, 15, кв. 12, Львов', NULL),
(6, 'ORD-003', 'processing', 879.92, 'cash_on_delivery', 'ул. Леси Украинки, 22, кв. 7, Днепр', 'Позвонить за час до доставки'),
(7, 'ORD-004', 'pending', 449.95, 'credit_card', 'ул. Сагайдачного, 5, кв. 3, Киев', NULL),
(8, 'ORD-005', 'delivered', 629.93, 'bank_transfer', 'ул. Хмельницкого, 8, кв. 15, Харьков', NULL),
(4, 'ORD-006', 'delivered', 319.96, 'credit_card', 'ул. Шевченко, 10, кв. 5, Киев', NULL),
(5, 'ORD-007', 'cancelled', 259.97, 'cash_on_delivery', 'ул. Франко, 15, кв. 12, Львов', 'Клиент отменил заказ'),
(6, 'ORD-008', 'delivered', 419.96, 'credit_card', 'ул. Леси Украинки, 22, кв. 7, Днепр', NULL),
(7, 'ORD-009', 'processing', 579.95, 'bank_transfer', 'ул. Сагайдачного, 5, кв. 3, Киев', NULL),
(8, 'ORD-010', 'pending', 499.96, 'cash_on_delivery', 'ул. Хмельницкого, 8, кв. 15, Харьков', 'Предварительно согласовать время');

-- Вставка товаров в заказах
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 3, 89.99), (1, 4, 2, 69.99), (1, 7, 1, 109.99),
(2, 2, 2, 79.99), (2, 5, 2, 59.99), (2, 10, 1, 139.99),
(3, 3, 3, 99.99), (3, 8, 2, 119.99), (3, 12, 2, 149.99),
(4, 1, 1, 89.99), (4, 2, 1, 79.99), (4, 3, 1, 99.99), (4, 4, 1, 69.99), (4, 5, 1, 59.99), (4, 6, 1, 69.99),
(5, 7, 2, 109.99), (5, 8, 2, 119.99), (5, 9, 1, 129.99), (5, 10, 1, 139.99),
(6, 1, 2, 89.99), (6, 4, 2, 69.99),
(7, 2, 1, 79.99), (7, 5, 3, 59.99),
(8, 3, 2, 99.99), (8, 11, 1, 129.99), (8, 6, 1, 69.99),
(9, 7, 1, 109.99), (9, 8, 1, 119.99), (9, 9, 1, 129.99), (9, 10, 1, 139.99), (9, 11, 1, 129.99),
(10, 12, 2, 149.99), (10, 3, 2, 99.99);

-- Вставка данных о движении товаров
INSERT INTO inventory_movements (product_id, warehouse_id, quantity, movement_type, reference_id, reference_type, notes, created_by) VALUES
(1, 1, 100, 'incoming', NULL, NULL, 'Начальное поступление', 3),
(2, 1, 120, 'incoming', NULL, NULL, 'Начальное поступление', 3),
(3, 1, 80, 'incoming', NULL, NULL, 'Начальное поступление', 3),
(4, 1, 90, 'incoming', NULL, NULL, 'Начальное поступление', 3),
(5, 1, 110, 'incoming', NULL, NULL, 'Начальное поступление', 3),
(1, 1, -3, 'outgoing', 1, 'order', 'Списание по заказу ORD-001', 2),
(4, 1, -2, 'outgoing', 1, 'order', 'Списание по заказу ORD-001', 2),
(7, 1, -1, 'outgoing', 1, 'order', 'Списание по заказу ORD-001', 2),
(2, 1, -2, 'outgoing', 2, 'order', 'Списание по заказу ORD-002', 2),
(5, 1, -2, 'outgoing', 2, 'order', 'Списание по заказу ORD-002', 2),
(10, 1, -1, 'outgoing', 2, 'order', 'Списание по заказу ORD-002', 2),
(3, 1, -3, 'outgoing', 3, 'order', 'Списание по заказу ORD-003', 2),
(8, 1, -2, 'outgoing', 3, 'order', 'Списание по заказу ORD-003', 2),
(12, 1, -2, 'outgoing', 3, 'order', 'Списание по заказу ORD-003', 2),
(1, 2, 50, 'adjustment', NULL, NULL, 'Коррекция инвентаря', 3),
(2, 2, 60, 'adjustment', NULL, NULL, 'Коррекция инвентаря', 3);

-- Вставка акций и скидок
INSERT INTO promotions (name, description, discount_type, discount_value, start_date, end_date, is_active, created_by) VALUES
('Летняя распродажа', 'Скидка на все фруктовые соки', 'percentage', 15.00, '2025-06-01', '2025-08-31', TRUE, 1),
('Черная пятница', 'Специальные цены на премиум продукты', 'percentage', 25.00, '2025-11-29', '2025-11-30', FALSE, 1),
('Акция дня', 'Фиксированная скидка на овощные соки', 'fixed_amount', 20.00, '2025-04-01', '2025-04-30', TRUE, 1);

-- Связь акций с продуктами
INSERT INTO promotion_products (promotion_id, product_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 7), (2, 8), (2, 12),
(3, 4), (3, 5), (3, 6);

-- Данные для аналитики продаж (за последние 6 месяцев)
-- 2024-11
INSERT INTO sales_analytics (date, product_id, quantity_sold, revenue, cost, profit) VALUES
('2024-11-01', 1, 12, 1079.88, 647.93, 431.95),
('2024-11-01', 2, 10, 799.90, 399.95, 399.95),
('2024-11-01', 3, 8, 799.92, 439.96, 359.96),
('2024-11-01', 4, 7, 489.93, 244.97, 244.96),
('2024-11-01', 5, 9, 539.91, 269.96, 269.95),
('2024-11-15', 1, 14, 1259.86, 755.92, 503.94),
('2024-11-15', 2, 11, 879.89, 439.95, 439.94),
('2024-11-15', 3, 9, 899.91, 494.95, 404.96),
('2024-11-15', 4, 8, 559.92, 279.96, 279.96),
('2024-11-15', 5, 10, 599.90, 299.95, 299.95);

-- 2024-12
INSERT INTO sales_analytics (date, product_id, quantity_sold, revenue, cost, profit) VALUES
('2024-12-01', 1, 18, 1619.82, 971.89, 647.93),
('2024-12-01', 2, 15, 1199.85, 599.93, 599.92),
('2024-12-01', 3, 12, 1199.88, 659.93, 539.95),
('2024-12-01', 4, 10, 699.90, 349.95, 349.95),
('2024-12-01', 5, 14, 839.86, 419.93, 419.93),
('2024-12-15', 1, 22, 1979.78, 1187.87, 791.91),
('2024-12-15', 2, 18, 1439.82, 719.91, 719.91),
('2024-12-15', 3, 16, 1599.84, 879.91, 719.93),
('2024-12-15', 4, 12, 839.88, 419.94, 419.94),
('2024-12-15', 5, 15, 899.85, 449.93, 449.92);

-- 2025-01
INSERT INTO sales_analytics (date, product_id, quantity_sold, revenue, cost, profit) VALUES
('2025-01-01', 6, 8, 559.92, 279.96, 279.96),
('2025-01-01', 7, 10, 1099.90, 549.95, 549.95),
('2025-01-01', 8, 9, 1079.91, 539.96, 539.95),
('2025-01-01', 9, 6, 779.94, 389.97, 389.97),
('2025-01-01', 10, 7, 979.93, 489.97, 489.96),
('2025-01-15', 6, 9, 629.91, 314.96, 314.95),
('2025-01-15', 7, 12, 1319.88, 659.94, 659.94),
('2025-01-15', 8, 10, 1199.90, 599.95, 599.95),
('2025-01-15', 9, 7, 909.93, 454.97, 454.96),
('2025-01-15', 10, 8, 1119.92, 559.96, 559.96);

-- 2025-02
INSERT INTO sales_analytics (date, product_id, quantity_sold, revenue, cost, profit) VALUES
('2025-02-01', 1, 15, 1349.85, 809.91, 539.94),
('2025-02-01', 2, 13, 1039.87, 519.94, 519.93),
('2025-02-01', 3, 10, 999.90, 549.95, 449.95),
('2025-02-01', 11, 7, 909.93, 454.97, 454.96),
('2025-02-01', 12, 5, 749.95, 374.98, 374.97),
('2025-02-15', 1, 16, 1439.84, 863.90, 575.94),
('2025-02-15', 2, 14, 1119.86, 559.93, 559.93),
('2025-02-15', 3, 12, 1199.88, 659.93, 539.95),
('2025-02-15', 11, 9, 1169.91, 584.96, 584.95),
('2025-02-15', 12, 6, 899.94, 449.97, 449.97);

-- 2025-03
INSERT INTO sales_analytics (date, product_id, quantity_sold, revenue, cost, profit) VALUES
('2025-03-01', 4, 14, 979.86, 489.93, 489.93),
('2025-03-01', 5, 16, 959.84, 479.92, 479.92),
('2025-03-01', 6, 10, 699.90, 349.95, 349.95),
('2025-03-01', 7, 8, 879.92, 439.96, 439.96),
('2025-03-01', 8, 7, 839.93, 419.97, 419.96),
('2025-03-15', 4, 15, 1049.85, 524.93, 524.92),
('2025-03-15', 5, 17, 1019.83, 509.92, 509.91),
('2025-03-15', 6, 11, 769.89, 384.95, 384.94),
('2025-03-15', 7, 9, 989.91, 494.96, 494.95),
('2025-03-15', 8, 8, 959.92, 479.96, 479.96);

-- 2025-04
INSERT INTO sales_analytics (date, product_id, quantity_sold, revenue, cost, profit) VALUES
('2025-04-01', 9, 9, 1169.91, 584.96, 584.95),
('2025-04-01', 10, 10, 1399.90, 699.95, 699.95),
('2025-04-01', 11, 8, 1039.92, 519.96, 519.96),
('2025-04-01', 12, 7, 1049.93, 524.97, 524.96),
('2025-04-15', 9, 10, 1299.90, 649.95, 649.95),
('2025-04-15', 10, 11, 1539.89, 769.95, 769.94),
('2025-04-15', 11, 9, 1169.91, 584.96, 584.95),
('2025-04-15', 12, 8, 1199.92, 599.96, 599.96);


ALTER TABLE order_items ADD COLUMN warehouse_id INT NULL AFTER price;

-- phpMyAdmin SQL Dump
-- version 4.7.5
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Май 22 2025 г., 16:40
-- Версия сервера: 10.1.36-MariaDB
-- Версия PHP: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `wincc`
--

-- --------------------------------------------------------

--
-- Структура таблицы `data`
--

CREATE TABLE `data` (
  `ID` int(10) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Parameter` decimal(10,2) NOT NULL,
  `Dates` varchar(17) NOT NULL DEFAULT '0000-00-00',
  `Times` varchar(17) NOT NULL DEFAULT '00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `data`
--

INSERT INTO `data` (`ID`, `Name`, `Parameter`, `Dates`, `Times`) VALUES
(3223, 'Температура пастерізації соку', '71.99', '22.05.2025', '16:34:59'),
(3224, 'Температура пастерізації соку', '71.98', '22.05.2025', '16:35:05'),
(3225, 'Температура пастерізації соку', '71.97', '22.05.2025', '16:35:11'),
(3226, 'Температура пастерізації соку', '71.98', '22.05.2025', '16:35:16'),
(3227, 'Температура пастерізації соку', '71.98', '22.05.2025', '16:35:21'),
(3228, 'Температура пастерізації соку', '71.99', '22.05.2025', '16:35:26'),
(3229, 'Температура пастерізації соку', '71.99', '22.05.2025', '16:35:31'),
(3230, 'Температура пастерізації соку', '72.00', '22.05.2025', '16:35:35'),
(3231, 'Температура пастерізації соку', '72.00', '22.05.2025', '16:35:39'),
(3232, 'Температура пастерізації соку', '72.01', '22.05.2025', '16:35:43'),
(3233, 'Температура пастерізації соку', '72.01', '22.05.2025', '16:35:47'),
(3176, 'Температура пастерізації соку', '50.00', '22.05.2025', '16:14:02'),
(3177, 'Температура пастерізації соку', '50.00', '22.05.2025', '16:20:05'),
(3178, 'Температура пастерізації соку', '50.00', '22.05.2025', '16:29:39'),
(3179, 'Температура пастерізації соку', '51.26', '22.05.2025', '16:31:11'),
(3180, 'Температура пастерізації соку', '56.75', '22.05.2025', '16:31:16'),
(3181, 'Температура пастерізації соку', '63.42', '22.05.2025', '16:31:21'),
(3182, 'Температура пастерізації соку', '69.82', '22.05.2025', '16:31:26'),
(3183, 'Температура пастерізації соку', '74.78', '22.05.2025', '16:31:31'),
(3184, 'Температура пастерізації соку', '78.03', '22.05.2025', '16:31:36'),
(3185, 'Температура пастерізації соку', '79.57', '22.05.2025', '16:31:41'),
(3186, 'Температура пастерізації соку', '79.55', '22.05.2025', '16:31:46'),
(3187, 'Температура пастерізації соку', '78.42', '22.05.2025', '16:31:52'),
(3188, 'Температура пастерізації соку', '76.61', '22.05.2025', '16:31:57'),
(3189, 'Температура пастерізації соку', '74.52', '22.05.2025', '16:32:02'),
(3190, 'Температура пастерізації соку', '72.60', '22.05.2025', '16:32:07'),
(3191, 'Температура пастерізації соку', '71.07', '22.05.2025', '16:32:12'),
(3192, 'Температура пастерізації соку', '70.05', '22.05.2025', '16:32:17'),
(3193, 'Температура пастерізації соку', '69.58', '22.05.2025', '16:32:23'),
(3194, 'Температура пастерізації соку', '69.60', '22.05.2025', '16:32:28'),
(3195, 'Температура пастерізації соку', '69.98', '22.05.2025', '16:32:33'),
(3196, 'Температура пастерізації соку', '70.56', '22.05.2025', '16:32:38'),
(3197, 'Температура пастерізації соку', '71.22', '22.05.2025', '16:32:43'),
(3198, 'Температура пастерізації соку', '71.82', '22.05.2025', '16:32:49'),
(3199, 'Температура пастерізації соку', '72.32', '22.05.2025', '16:32:54'),
(3200, 'Температура пастерізації соку', '72.63', '22.05.2025', '16:32:59'),
(3201, 'Температура пастерізації соку', '72.77', '22.05.2025', '16:33:04'),
(3202, 'Температура пастерізації соку', '72.76', '22.05.2025', '16:33:09'),
(3203, 'Температура пастерізації соку', '72.64', '22.05.2025', '16:33:14'),
(3204, 'Температура пастерізації соку', '72.45', '22.05.2025', '16:33:18'),
(3205, 'Температура пастерізації соку', '72.24', '22.05.2025', '16:33:23'),
(3206, 'Температура пастерізації соку', '72.05', '22.05.2025', '16:33:28'),
(3207, 'Температура пастерізації соку', '71.90', '22.05.2025', '16:33:34'),
(3208, 'Температура пастерізації соку', '71.80', '22.05.2025', '16:33:39'),
(3209, 'Температура пастерізації соку', '71.75', '22.05.2025', '16:33:45'),
(3210, 'Температура пастерізації соку', '71.76', '22.05.2025', '16:33:51'),
(3211, 'Температура пастерізації соку', '71.80', '22.05.2025', '16:33:56'),
(3212, 'Температура пастерізації соку', '71.86', '22.05.2025', '16:34:02'),
(3213, 'Температура пастерізації соку', '71.93', '22.05.2025', '16:34:06'),
(3214, 'Температура пастерізації соку', '71.99', '22.05.2025', '16:34:11'),
(3215, 'Температура пастерізації соку', '72.03', '22.05.2025', '16:34:15'),
(3216, 'Температура пастерізації соку', '72.07', '22.05.2025', '16:34:20'),
(3217, 'Температура пастерізації соку', '72.08', '22.05.2025', '16:34:25'),
(3218, 'Температура пастерізації соку', '72.08', '22.05.2025', '16:34:30'),
(3219, 'Температура пастерізації соку', '72.06', '22.05.2025', '16:34:35'),
(3220, 'Температура пастерізації соку', '72.04', '22.05.2025', '16:34:41'),
(3221, 'Температура пастерізації соку', '72.02', '22.05.2025', '16:34:46'),
(3222, 'Температура пастерізації соку', '72.00', '22.05.2025', '16:34:52');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `data`
--
ALTER TABLE `data`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `data`
--
ALTER TABLE `data`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3234;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
