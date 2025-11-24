<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Crochet Online Shop</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="../index.php">🧶 Crochet Online Shop</a></h1>
            <nav>
                <a href="../index.php">Home</a>
                <a href="shop.php">Shop</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
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
        <h2>About Crochet Online Shop</h2>
        
        <div class="about-content">
            <section class="about-story">
                <h3>Our Story</h3>
                <p>Welcome to Crochet Online Shop, where every stitch tells a story of love, creativity, and craftsmanship. Founded with a passion for handmade crochet items, we bring you unique, high-quality products that are made with care and attention to detail.</p>
                
                <p>Our journey began with a simple love for crochet and the desire to share beautiful, handcrafted items with the world. Each product in our collection is carefully created by skilled artisans who pour their heart into every piece.</p>
            </section>
            
            <section class="about-mission">
                <h3>Our Mission</h3>
                <p>We believe in the beauty of handmade items and the joy they bring to people's lives. Our mission is to:</p>
                <ul>
                    <li>Provide high-quality, handcrafted crochet products</li>
                    <li>Support local artisans and their craft</li>
                    <li>Bring joy and comfort through our creations</li>
                    <li>Promote the art of crochet and handmade crafts</li>
                </ul>
            </section>
            
            <section class="about-products">
                <h3>Our Products</h3>
                <p>We offer a wide range of crochet items including:</p>
                <ul>
                    <li><strong>Amigurumi:</strong> Adorable stuffed animals and characters</li>
                    <li><strong>Clothing:</strong> Sweaters, scarves, hats, and more</li>
                    <li><strong>Accessories:</strong> Bags, purses, and fashion accessories</li>
                    <li><strong>Home Decor:</strong> Blankets, pillows, and decorative items</li>
                    <li><strong>Baby Items:</strong> Soft and safe products for little ones</li>
                </ul>
            </section>
            
            <section class="about-quality">
                <h3>Quality Promise</h3>
                <p>Every item is made with premium yarns and materials, ensuring durability and comfort. We take pride in our craftsmanship and stand behind the quality of our products.</p>
            </section>
        </div>
    </main>
</body>
</html>
