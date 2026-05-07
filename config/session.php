<?php
// ============================================================
//  config/session.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /index.php');
        exit;
    }
}

function requireAdmin(): void {
    requireAuth();
    if ((int)$_SESSION['rol_id'] !== 1) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode(['ok' => false, 'msg' => 'Acceso denegado.']));
    }
}

function isAdmin(): bool {
    return isset($_SESSION['rol_id']) && (int)$_SESSION['rol_id'] === 1;
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function clean(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// ← FUNCIÓN MOVIDA AQUÍ para que esté disponible en ventas.php
function generarFolio(PDO $db): string {
    $year = date('Y');
    $stmt = $db->prepare("SELECT COUNT(*) FROM ventas WHERE YEAR(created_at) = ?");
    $stmt->execute([$year]);
    $count = (int)$stmt->fetchColumn() + 1;
    return sprintf('VTA-%s-%04d', $year, $count);
}