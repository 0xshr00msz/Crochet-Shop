<?php
// Database setup script
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    
    echo "✅ Database setup completed successfully!\n";
    echo "📝 Sample products have been added.\n";
    echo "🚀 You can now run the application.\n";
    
} catch(PDOException $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
}
?>
