CREATE DATABASE IF NOT EXISTS crochet_onlineshop;
USE crochet_onlineshop;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    category ENUM('amigurumi', 'clothing', 'accessories', 'home_decor', 'bags') NOT NULL,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert sample products
INSERT INTO products (name, description, price, stock, category, image, is_featured) VALUES
('Cute Teddy Bear', 'Handmade crochet teddy bear, perfect for kids', 450.00, 10, 'amigurumi', 'teddy.jpg', TRUE),
('Baby Blanket', 'Soft and cozy baby blanket in pastel colors', 850.00, 5, 'home_decor', 'blanket.jpg', TRUE),
('Crochet Bag', 'Stylish tote bag made with cotton yarn', 320.00, 8, 'bags', 'bag.jpg', TRUE),
('Winter Scarf', 'Warm and fashionable winter scarf', 280.00, 15, 'accessories', 'scarf.jpg', FALSE),
('Baby Booties', 'Adorable booties for newborns', 180.00, 20, 'clothing', 'booties.jpg', TRUE),
('Flower Pot Cover', 'Decorative cover for small flower pots', 150.00, 12, 'home_decor', 'pot_cover.jpg', FALSE);
