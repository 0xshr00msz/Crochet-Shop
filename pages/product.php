<?php
session_start();
require_once '../config/database.php';
require_once '../includes/shopalgorithms.php';

$shop = new ShopAlgorithms($pdo);
$productId = $_GET['id'] ?? 0;
$product = $shop->getProduct($productId);

if (!$product) {
    header('Location: shop.php');
    exit;
}

$message = '';

if ($_POST && isset($_SESSION['user_id'])) {
    $quantity = $_POST['quantity'] ?? 1;
    
    if ($shop->addToCart($_SESSION['user_id'], $productId, $quantity)) {
        $message = 'Product added to cart!';
        $_SESSION['cart_count'] = $shop->getCartCount($_SESSION['user_id']);
    } else {
        $message = 'Failed to add product to cart.';
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
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="../index.php">🧶 Crochet Online Shop</a></h1>
            <nav>
                <a href="../index.php">Home</a>
                <a href="shop.php">Shop</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php">Cart 
                        <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                            <span class="badge"><?= $_SESSION['cart_count'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="account.php">My Account</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if ($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="product-detail">
            <div class="product-image">
                <?php if ($product['image']): ?>
                    <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <div class="no-image">📷 No Image Available</div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <p class="category">Category: <?= ucfirst(str_replace('_', ' ', $product['category'])) ?></p>
                <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                <p class="stock">Stock: <?= $product['stock'] ?> available</p>

                <?php if ($product['description']): ?>
                    <div class="description">
                        <h3>Description</h3>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="product-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($product['stock'] > 0): ?>
                            <form method="POST" class="add-to-cart-form">
                                <div class="quantity-selector">
                                    <label for="quantity">Quantity:</label>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <p class="out-of-stock">Out of Stock</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><a href="login.php" class="btn btn-primary">Login to Purchase</a></p>
                    <?php endif; ?>
                </div>

                <div class="product-navigation">
                    <a href="shop.php" class="btn btn-secondary">← Back to Shop</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
