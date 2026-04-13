CREATE DATABASE IF NOT EXISTS kkstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kkstore;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_path VARCHAR(255) DEFAULT NULL,
    role ENUM('customer','manager','admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    brand VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    category_id INT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS product_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manager_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(180) NOT NULL,
    brand VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_requests_manager FOREIGN KEY (manager_id) REFERENCES users(id),
    CONSTRAINT fk_requests_category FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS repair_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_type VARCHAR(80) NOT NULL,
    brand VARCHAR(80) NOT NULL,
    model VARCHAR(120) NOT NULL,
    issue_description TEXT NOT NULL,
    contact_phone VARCHAR(40) NOT NULL,
    preferred_date DATE DEFAULT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    decision_reason TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_repair_user FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO users (name, email, password_hash, role) VALUES
('Администратор', 'admin@kkstore.local', '$2y$12$TsyikEXrYCG.OfgcGa4GJu5e/weUYDiI1re8CBV1WF03G/00Jej5e', 'admin'),
('Менеджер', 'manager@kkstore.local', '$2y$12$TsyikEXrYCG.OfgcGa4GJu5e/weUYDiI1re8CBV1WF03G/00Jej5e', 'manager')
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO categories (title, slug) VALUES
('Смартфоны', 'smartfony'),
('Планшеты', 'planshety'),
('Ноутбуки', 'noutbuki'),
('Аксессуары', 'aksessuary'),
('Комплектующие', 'komplektuyushchie')
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO products (name, brand, description, image_path, price, category_id, stock, is_featured) VALUES
('Galaxy S25 256GB', 'Samsung', 'Флагманский смартфон с AMOLED-дисплеем, AI-камерами и быстрой зарядкой.', NULL, 89990, 1, 12, 1),
('iPhone 17 128GB', 'Apple', 'Новая линейка iPhone с улучшенной автономностью, производительностью и безопасностью.', NULL, 109990, 1, 8, 1),
('Redmi Note 16 Pro', 'Xiaomi', 'Сбалансированный смартфон для ежедневных задач с емким аккумулятором.', NULL, 34990, 1, 22, 0),
('iPad Air 13', 'Apple', 'Планшет для учебы и работы с поддержкой клавиатуры и стилуса.', NULL, 79990, 2, 6, 1),
('MatePad Pro', 'Huawei', 'Производительный планшет с качественным экраном и аудиосистемой.', NULL, 49990, 2, 9, 0),
('ROG Zephyrus G16', 'ASUS', 'Игровой ноутбук для тяжелых задач, монтажа и современных игр.', NULL, 189990, 3, 4, 1),
('MacBook Air M5', 'Apple', 'Легкий ноутбук с энергоэффективным чипом и долгой автономной работой.', NULL, 149990, 3, 5, 1),
('PowerBank 20000mAh', 'Baseus', 'Быстрая зарядка нескольких устройств в дороге.', NULL, 3990, 4, 35, 0),
('Чехол MagSafe', 'Spigen', 'Надежный чехол с поддержкой беспроводной зарядки.', NULL, 2490, 4, 40, 0),
('SSD NVMe 1TB', 'Kingston', 'Высокоскоростной накопитель для апгрейда ПК и ноутбуков.', NULL, 8990, 5, 15, 0);

INSERT INTO news (title, content) VALUES
('Открыт новый сервисный пост', 'Мы расширили сервисный центр и сократили среднее время ремонта смартфонов до 2 часов. Также внедрили систему фото-отчета по сложным заказам.'),
('Весенняя акция на аксессуары', 'До конца месяца действует скидка 20% на защитные стекла и чехлы при покупке смартфона. На комплект "стекло + чехол + настройка" действует спеццена.'),
('Корпоративное обслуживание', 'Запущен тариф для компаний: приоритетная поддержка, персональный инженер, выездная диагностика и отчеты для бухгалтерии.');
