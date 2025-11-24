<?php
session_start();
require_once '../config/database.php';
require_once '../includes/ShopAlgorithms.php';

$shop = new ShopAlgorithms($pdo);

$productId = $_GET['id'] ?? 0;
$product = $shop->getProduct($productId);

if (!$product) {
    header('Location: shop.php');
    exit;
}

$images = $shop->getProductImages($productId);

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = 'Please login to add items to cart';
        header('Location: login.php');
        exit;
    }
    
    $quantity = $_POST['quantity'] ?? 1;
    $result = $shop->addToCart($_SESSION['user_id'], $productId, $quantity);
    $_SESSION['message'] = $result['message'];
    
    if ($result['success']) {
        header('Location: cart.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Crochet Online Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            padding: 3rem 0;
        }
        .product-images {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .main-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-info h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .price {
            font-size: 2.5rem;
            color: #ff6b9d;
            font-weight: bold;
            margin: 1rem 0;
        }
        .stock-info {
            color: #28a745;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        .out-of-stock {
            color: #dc3545;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 2rem 0;
        }
        .quantity-selector input {
            width: 80px;
            padding: 0.5rem;
            font-size: 1.2rem;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        .description {
            margin: 2rem 0;
            line-height: 1.8;
            color: #666;
        }
        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1><a href="../index.php" style="color: white; text-decoration: none;">🧶 Crochet Online Shop</a></h1>
            <nav>
                <a href="../index.php">Home</a>
                <a href="shop.php">Shop</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php">Cart</a>
                    <a href="account.php">My Account</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="product-detail">
            <!-- Images -->
            <div class="product-images">
                <div class="main-image">
                    <?php if (!empty($images)): ?>
                        <img src="../uploads/<?= htmlspecialchars($images[0]['filename']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php else: ?>
                        <img src="../uploads/placeholder.jpg" alt="No image">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <p style="color: #999; font-size: 1.1rem; text-transform: capitalize;">
                    <?= str_replace('_', ' ', $product['category']) ?>
                </p>
                
                <?php if ($product['review_count'] > 0): ?>
                    <p style="color: #ffa500; font-size: 1.1rem; margin: 0.5rem 0;">
                        ⭐ <?= number_format($product['avg_rating'], 1) ?> (<?= $product['review_count'] ?> reviews)
                    </p>
                <?php endif; ?>
                
                <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                
                <?php if ($product['stock'] > 0): ?>
                    <p class="stock-info">✓ In Stock (<?= $product['stock'] ?> available)</p>
                <?php else: ?>
                    <p class="stock-info out-of-stock">✗ Out of Stock</p>
                <?php endif; ?>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert">
                        <?= htmlspecialchars($_SESSION['message']) ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <!-- Add to Cart Form -->
                <?php if ($product['stock'] > 0): ?>
                <form method="POST">
                    <div class="quantity-selector">
                        <label style="font-weight: bold;">Quantity:</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" required>
                    </div>
                    <button type="submit" name="add_to_cart" class="btn btn-primary" style="font-size: 1.2rem; padding: 1rem 2rem;">
                        🛒 Add to Cart
                    </button>
                </form>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled style="font-size: 1.2rem; padding: 1rem 2rem;">
                        Out of Stock
                    </button>
                <?php endif; ?>

                <!-- Description -->
                <div class="description">
                    <h3>Product Description</h3>
                    <p><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></p>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin: 2rem 0;">
            <a href="shop.php" class="btn btn-secondary">← Back to Shop</a>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>