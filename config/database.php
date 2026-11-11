<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'sqlite:' . DB_PATH;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$pdo = new PDO($dsn, null, null, $options);
        }
        return self::$pdo;
    }

    public static function init(): void
    {
        $pdo = self::getConnection();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                color TEXT DEFAULT '#6366f1',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                sku TEXT NOT NULL UNIQUE,
                category_id INTEGER,
                quantity INTEGER NOT NULL DEFAULT 0,
                min_stock INTEGER NOT NULL DEFAULT 5,
                unit TEXT DEFAULT 'шт',
                price REAL NOT NULL DEFAULT 0,
                location TEXT,
                description TEXT,
                status TEXT DEFAULT 'active',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            );

            CREATE TABLE IF NOT EXISTS movements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item_id INTEGER NOT NULL,
                type TEXT NOT NULL CHECK(type IN ('in','out','adjust')),
                quantity INTEGER NOT NULL,
                reason TEXT,
                reference TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
            );

            CREATE INDEX IF NOT EXISTS idx_items_sku ON items(sku);
            CREATE INDEX IF NOT EXISTS idx_items_category ON items(category_id);
            CREATE INDEX IF NOT EXISTS idx_items_status ON items(status);
            CREATE INDEX IF NOT EXISTS idx_movements_item ON movements(item_id);
            CREATE INDEX IF NOT EXISTS idx_movements_created ON movements(created_at);
        ");

        $seed = [
            ['Электроника', '#3b82f6'],
            ['Мебель', '#10b981'],
            ['Канцелярия', '#f59e0b'],
            ['Инструменты', '#ef4444'],
            ['Сантехника', '#8b5cf6'],
            ['Электрика', '#ec4899'],
        ];
        foreach ($seed as $cat) {
            try {
                $pdo->prepare("INSERT INTO categories (name, color) VALUES (?, ?)")
                    ->execute($cat);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }

        $items = [
            ['Ноутбук MacBook Pro 14', 'NB-001', 1, 12, 3, 'шт', 1899.00, 'Стеллаж A-1', 'Apple M3 Pro, 18ГБ RAM, 512ГБ SSD'],
            ['Стул офисный эргономичный', 'OF-045', 2, 24, 5, 'шт', 124.50, 'Стеллаж B-3', 'Тканевая обивка, регулировка высоты'],
            ['Бумага формата A4 80г/м2', 'CS-102', 3, 500, 100, 'пачка', 4.20, 'Стеллаж C-1', '500 листов, белая'],
            ['Отвертка крестовая Phillips', 'TL-078', 4, 42, 10, 'шт', 8.90, 'Стеллаж D-2', 'Магнитная насадка, резиновая рукоятка'],
            ['Смеситель для раковины хром', 'PL-033', 5, 18, 4, 'шт', 45.00, 'Стеллаж E-1', 'Однорычажный, нержавеющая сталь'],
            ['Автомат выключатель 16А', 'EL-056', 6, 36, 8, 'шт', 12.75, 'Стеллаж F-2', 'Однополюсный, тип C'],
            ['Монитор 27 дюймов 4K', 'MN-011', 1, 8, 2, 'шт', 349.00, 'Стеллаж A-2', 'IPS, USB-C, HDR10'],
            ['Компьютерная мышь беспроводная', 'PC-210', 1, 45, 10, 'шт', 22.00, 'Стеллаж A-3', '2.4ГГц, эргономичная'],
        ];
        foreach ($items as $it) {
            try {
                $stmt = $pdo->prepare("INSERT INTO items (name, sku, category_id, quantity, min_stock, unit, price, location, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute($it);
                $itemId = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO movements (item_id, type, quantity, reason, reference) VALUES (?, 'in', ?, 'Первичное поступление', 'INIT-001')")
                    ->execute([$itemId, $it[4]]);
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }
    }
}
