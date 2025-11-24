<?php
session_start();
require_once './config/database.php';
require_once './includes/shopalgorithms.php';

$shop = new ShopAlgorithms($pdo);
$featuredProducts = $shop->getFeaturedProducts(6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crochet Online Shop - Handmade Crochet Products</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1>🧶 Crochet Online Shop</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pages/shop.php">Shop</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="pages/cart.php">Cart 
                        <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                            <span class="badge"><?= $_SESSION['cart_count'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="pages/account.php">My Account</a>
                    <a href="pages/logout.php">Logout</a>
                <?php else: ?>
                    <a href="pages/login.php">Login</a>
                    <a href="pages/register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Handmade Crochet Products with Love ❤️</h2>
            <p>Discover unique, handcrafted crochet items made just for you</p>
            <a href="pages/shop.php" class="btn btn-primary">Shop Now</a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2>✨ Featured Products</h2>
            <div class="product-grid">
                <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card">
                    <?php if ($product['image']): ?>
                        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php else: ?>
                        <img src="uploads/placeholder.jpg" alt="No image">
                    <?php endif; ?>
                    
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="category"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></p>
                    <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                    
                    <a href="pages/product.php?id=<?= $product['id'] ?>" class="btn btn-secondary">View Details</a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($featuredProducts)): ?>
                <p>No featured products at the moment. Check back soon!</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>