<?php
session_start();
require_once '../config/database.php';
require_once '../includes/ShopAlgorithms.php';

$shop = new ShopAlgorithms($pdo);

// Get filters from URL
$category = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
$sortBy = $_GET['sort'] ?? 'newest';
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

// Build filters
$filters = ['in_stock' => true];
if ($category) $filters['category'] = $category;
if ($minPrice) $filters['min_price'] = $minPrice;
if ($maxPrice) $filters['max_price'] = $maxPrice;

// Get products
if ($search) {
    $products = $shop->searchProducts($search);
} else {
    $products = $shop->getProducts($filters, $sortBy, $page, 12);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Crochet Online Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .shop-header {
            padding: 2rem 0;
            background: #f8f9fa;
        }
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filter-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        .filter-group label {
            font-size: 0.9rem;
            font-weight: bold;
            color: #666;
        }
        .filter-group select,
        .filter-group input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        .results-info {
            margin: 1rem 0;
            color: #666;
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

    <div class="shop-header">
        <div class="container">
            <h2>Shop All Products</h2>
        </div>
    </div>

    <div class="container">
        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="shop.php">
                <div class="filter-row">
                    <!-- Search -->
                    <div class="filter-group search-box">
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <!-- Category -->
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="bags" <?= $category=='bags'?'selected':'' ?>>Bags</option>
                            <option value="keychains" <?= $category=='keychains'?'selected':'' ?>>Keychains</option>
                            <option value="hats" <?= $category=='hats'?'selected':'' ?>>Hats</option>
                            <option value="tops" <?= $category=='tops'?'selected':'' ?>>Tops</option>
                            <option value="accessories" <?= $category=='accessories'?'selected':'' ?>>Accessories</option>
                            <option value="home_decor" <?= $category=='home_decor'?'selected':'' ?>>Home Decor</option>
                            <option value="toys" <?= $category=='toys'?'selected':'' ?>>Toys</option>
                        </select>
                    </div>

                    <!-- Min Price -->
                    <div class="filter-group">
                        <label>Min Price</label>
                        <input type="number" name="min_price" placeholder="₱0" value="<?= $minPrice ?>" style="width: 100px;">
                    </div>

                    <!-- Max Price -->
                    <div class="filter-group">
                        <label>Max Price</label>
                        <input type="number" name="max_price" placeholder="₱9999" value="<?= $maxPrice ?>" style="width: 100px;">
                    </div>

                    <!-- Sort -->
                    <div class="filter-group">
                        <label>Sort By</label>
                        <select name="sort">
                            <option value="newest" <?= $sortBy=='newest'?'selected':'' ?>>Newest First</option>
                            <option value="price_asc" <?= $sortBy=='price_asc'?'selected':'' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $sortBy=='price_desc'?'selected':'' ?>>Price: High to Low</option>
                            <option value="name" <?= $sortBy=='name'?'selected':'' ?>>Name A-Z</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="results-info">
            <p>Showing <?= count($products) ?> products</p>
        </div>

        <!-- Products Grid -->
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $product['id'] ?>">
                    <?php if ($product['image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php else: ?>
                        <img src="../uploads/placeholder.jpg" alt="No image">
                    <?php endif; ?>
                </a>
                
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p class="category"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></p>
                
                <?php if ($product['review_count'] > 0): ?>
                    <p style="color: #ffa500; font-size: 0.9rem;">
                        ⭐ <?= number_format($product['avg_rating'], 1) ?> (<?= $product['review_count'] ?> reviews)
                    </p>
                <?php endif; ?>
                
                <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                <p style="color: #666; font-size: 0.9rem;">Stock: <?= $product['stock'] ?></p>
                
                <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-secondary">View Details</a>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; color: #999;">No products found matching your criteria.</p>
                <a href="shop.php" class="btn btn-primary" style="margin-top: 1rem;">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>