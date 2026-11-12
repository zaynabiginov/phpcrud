<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private const SESSION_NAME = 'magazine_sid';
    private static bool $inited = false;

    public static function init(): void
    {
        if (self::$inited) { return; }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            session_name(self::SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            session_start();
        }
        $pdo = Database::pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin','shop') NOT NULL DEFAULT 'shop',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $seed = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ((int) $seed === 0) {
            $ins = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            $ins->execute(['admin', password_hash('admin', PASSWORD_DEFAULT), 'admin']);
            $ins->execute(['shop', password_hash('shop', PASSWORD_DEFAULT), 'shop']);
        }
        self::$inited = true;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u && ($u['role'] ?? '') === 'admin';
    }

    public static function homeUrl(): string
    {
        return self::isAdmin() ? 'home' : 'warehouse';
    }

    public static function requireLogin(): void
    {
        if (!self::user()) {
            header('Location: ' . url('login'));
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        $u = self::user();
        if (!$u || ($u['role'] ?? '') !== $role) {
            http_response_code(403);
            header('Location: ' . url(self::homeUrl()) . '?err=forbidden');
            exit;
        }
    }

    public static function canEditStock(): bool
    {
        return self::isAdmin();
    }

    public static function canAddStock(): bool
    {
        $u = self::user();
        return $u && in_array($u['role'] ?? '', ['admin', 'shop'], true);
    }

    public static function canManageUsers(): bool
    {
        return self::isAdmin();
    }
}