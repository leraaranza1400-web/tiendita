<?php
// ============================================================
//  config/database.php
//  ⚠️  CAMBIA DB_PASS con tu contraseña real de MariaDB
// ============================================================

define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'tienda_db');
define('DB_USER',    'tiendita_user');
define('DB_PASS',    'Tiendita123!');        // ← PON AQUÍ TU CONTRASEÑA
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            //PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            // Mostrar mensaje genérico; loguear detalle internamente
            error_log('DB Error: ' . $e->getMessage());
            die(json_encode(['error' => 'Error de conexión a la base de datos.']));
        }
    }
    return $pdo;
}