<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Product
{
    public static function allWithStock(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query("
            SELECT p.id, p.name, p.description, p.unit, p.cost_unit, p.markup_pct,
                   COALESCE(SUM(CASE WHEN t.type='income' THEN t.quantity ELSE -t.quantity END), 0) AS stock_qty
            FROM products p
            LEFT JOIN transactions t ON t.product_id = p.id
            GROUP BY p.id, p.name, p.description, p.unit, p.cost_unit, p.markup_pct
            ORDER BY p.name
        ");
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $cost = max(0.0, (float) ($r['cost_unit'] ?? 0));
            $markup = max(0.0, (float) ($r['markup_pct'] ?? 0));
            $r['sale_price'] = round($cost * (100.0 + $markup) / 100.0, 2);
            $r['stock_qty'] = max(0.0, (float) ($r['stock_qty'] ?? 0));
            $r['unit'] = normalize_unit($r['unit'] ?? 'шт');
            $r['description'] = db_text($r['description'] ?? '');
            $r['name'] = db_text($r['name'] ?? '');
        }
        unset($r);
        return $rows;
    }

    public static function allBasic(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query("SELECT id, name, description, unit, cost_unit, markup_pct FROM products ORDER BY name");
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['name'] = db_text($r['name'] ?? '');
            $r['description'] = db_text($r['description'] ?? '');
            $r['unit'] = normalize_unit($r['unit'] ?? 'шт');
        }
        unset($r);
        return $rows;
    }

    public static function findForSale(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.unit, p.cost_unit, p.markup_pct,
                   COALESCE(SUM(CASE WHEN t.type='income' THEN t.quantity ELSE -t.quantity END), 0) AS stock_qty
            FROM products p
            LEFT JOIN transactions t ON t.product_id = p.id
            WHERE p.id = ?
            GROUP BY p.id, p.name, p.unit, p.cost_unit, p.markup_pct
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}