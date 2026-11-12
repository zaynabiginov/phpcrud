<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;
    private static bool $migrated = false;

    public static function pdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        $host = '127.0.0.1';
        $db   = 'magazine';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            $pdo->exec("SET NAMES utf8mb4");
            self::migrate($pdo);
            self::$pdo = $pdo;
            return $pdo;
        } catch (PDOException $e) {
            http_response_code(500);
            die('Ошибка подключения к базе данных: ' . htmlspecialchars($e->getMessage()));
        }
    }

    private static function migrate(PDO $pdo): void
    {
        if (self::$migrated) { return; }
        // quantity
        $cols = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'quantity'")->fetch(PDO::FETCH_ASSOC);
        if (!$cols) {
            $pdo->exec("ALTER TABLE transactions ADD COLUMN quantity DECIMAL(12,3) NOT NULL DEFAULT 1 AFTER product_id");
        } else {
            $type = strtolower((string)($cols['Type'] ?? ''));
            if (strpos($type, 'int') !== false) {
                $pdo->exec("ALTER TABLE transactions MODIFY COLUMN quantity DECIMAL(12,3) NOT NULL DEFAULT 1");
            }
        }
        // unit
        $ucols = $pdo->query("SHOW COLUMNS FROM products LIKE 'unit'")->fetchAll();
        if (!$ucols) {
            $pdo->exec("ALTER TABLE products ADD COLUMN unit VARCHAR(10) NOT NULL DEFAULT 'шт' AFTER description");
        }
        // user_id
        $tucols = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'user_id'")->fetchAll();
        if (!$tucols) {
            $pdo->exec("ALTER TABLE transactions ADD COLUMN user_id INT NULL DEFAULT NULL AFTER product_id");
        }
        // user_sessions
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            login_at DATETIME NOT NULL,
            logout_at DATETIME NULL DEFAULT NULL,
            ip VARCHAR(45) NULL DEFAULT NULL,
            INDEX idx_user_sessions_user (user_id),
            INDEX idx_user_sessions_login (login_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // cost/markup
        $pcols = $pdo->query("SHOW COLUMNS FROM products LIKE 'cost_unit'")->fetchAll();
        if (!$pcols) {
            $pdo->exec("ALTER TABLE products ADD COLUMN cost_unit DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER name");
            $pdo->exec("ALTER TABLE products ADD COLUMN markup_pct DECIMAL(6,2) NOT NULL DEFAULT 100 AFTER cost_unit");
        }
        // employees
        $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            birth_date DATE NULL DEFAULT NULL,
            birth_year SMALLINT NULL DEFAULT NULL,
            hire_date DATE NULL DEFAULT NULL,
            termination_date DATE NULL DEFAULT NULL,
            position VARCHAR(50) NOT NULL,
            user_id INT NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_employees_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $empCols = $pdo->query("SHOW COLUMNS FROM employees LIKE 'birth_date'")->fetchAll();
        if (!$empCols) {
            $pdo->exec("ALTER TABLE employees ADD COLUMN birth_date DATE NULL DEFAULT NULL AFTER full_name");
        }
        // description
        $dcols = $pdo->query("SHOW COLUMNS FROM products LIKE 'description'")->fetchAll();
        if (!$dcols) {
            $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT NULL AFTER name");
        }
        // category_id nullable + drop FK
        $catCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'category_id'")->fetch(PDO::FETCH_ASSOC);
        if ($catCol) {
            $fkRows = $pdo->query("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products'
                  AND COLUMN_NAME = 'category_id' AND REFERENCED_TABLE_NAME IS NOT NULL
            ")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($fkRows as $fkName) {
                $pdo->exec('ALTER TABLE products DROP FOREIGN KEY `' . str_replace('`', '``', $fkName) . '`');
            }
            $isNullable = strtoupper((string) ($catCol['Null'] ?? '')) === 'YES';
            $type = (string) ($catCol['Type'] ?? 'int(11)');
            if (!$isNullable) {
                $pdo->exec("ALTER TABLE products MODIFY COLUMN category_id {$type} NULL DEFAULT NULL");
            }
        }
    }
}
