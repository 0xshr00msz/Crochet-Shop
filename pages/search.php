<?php
session_start();
require_once '../config/database.php';
require_once '../includes/ShopAlgorithms.php';

$shop = new ShopAlgorithms($pdo);
$query = $_GET['q'] ?? '';
$products = [];

if ($query) {
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE name LIKE ? OR description LIKE ? OR category LIKE ?
        ORDER BY name
    ");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $products = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Crochet Online Shop</title>
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

    <main class="container">
        <div class="search-header">
            <h2>Search Results</h2>
            <form method="GET" class="search-form">
                <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Search products...">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
        
        <?php if ($query): ?>
            <p>Showing results for: <strong><?= htmlspecialchars($query) ?></strong></p>
            
            <?php if ($products): ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php if ($product['image']): ?>
                                <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <img src="../uploads/placeholder.jpg" alt="No image">
                            <?php endif; ?>
                            
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="category"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></p>
                            <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                            
                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-secondary">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No products found matching your search.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Enter a search term to find products.</p>
        <?php endif; ?>
    </main>
</body>
</html>
