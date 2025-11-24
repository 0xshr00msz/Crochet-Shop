<?php
session_start();
require_once '../config/database.php';
require_once '../includes/shopalgorithms.php';

$shop = new ShopAlgorithms($pdo);

$category = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
$sortBy = $_GET['sort'] ?? 'newest';
$search = $_GET['search'] ?? '';

$filters = ['in_stock' => true];
if ($category) $filters['category'] = $category;
if ($minPrice) $filters['min_price'] = $minPrice;
if ($maxPrice) $filters['max_price'] = $maxPrice;

if ($search) {
    $products = $shop->searchProducts($search);
} else {
    $products = $shop->getProducts($filters, $sortBy);
}

$categories = $shop->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Crochet Online Shop</title>
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
        <h1>🛍️ Shop</h1>

        <!-- Search and Filters -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="filter-row">
                    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                    
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $cat)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="number" name="min_price" placeholder="Min Price" value="<?= $minPrice ?>" step="0.01">
                    <input type="number" name="max_price" placeholder="Max Price" value="<?= $maxPrice ?>" step="0.01">
                    
                    <select name="sort">
                        <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>

        <!-- Products Grid -->
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <?php if ($product['image']): ?>
                    <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <div class="no-image">📷 No Image</div>
                <?php endif; ?>
                
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p class="category"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></p>
                <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                <p class="stock">Stock: <?= $product['stock'] ?></p>
                
                <div class="product-actions">
                    <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-secondary">View Details</a>
                    <?php if (isset($_SESSION['user_id']) && $product['stock'] > 0): ?>
                        <form method="POST" action="cart.php" style="display: inline;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn btn-primary">Add to Cart</button>
                        </form>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="btn btn-primary">Login to Buy</a>
                    <?php else: ?>
                        <button class="btn btn-disabled" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
            <div class="no-products">
                <h3>No products found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
