<?php
try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;dbname=tienda_db;charset=utf8mb4',
        'tiendita_user',
        '#1515#'
    );
    echo "✅ Conexión exitosa!";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}