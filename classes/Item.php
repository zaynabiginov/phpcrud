<?php
declare(strict_types=1);

class Item
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(?string $search = null, ?string $category = null, ?string $status = null, int $page = 1, int $perPage = 15): array
    {
        $sql = "SELECT i.*, c.name AS category_name, c.color AS category_color
                FROM items i
                LEFT JOIN categories c ON i.category_id = c.id
                WHERE 1=1";
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= " AND (i.name LIKE ? OR i.sku LIKE ? OR i.location LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($category !== null && $category !== '') {
            $sql .= " AND i.category_id = ?";
            $params[] = (int) $category;
        }
        if ($status !== null && $status !== '') {
            $sql .= " AND i.status = ?";
            $params[] = $status;
        }

        $countSql = "SELECT COUNT(*) FROM ({$sql})";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql .= " ORDER BY i.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return ['items' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => (int) ceil($total / $perPage)];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT i.*, c.name AS category_name, c.color AS category_color
            FROM items i
            LEFT JOIN categories c ON i.category_id = c.id
            WHERE i.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO items (name, sku, category_id, quantity, min_stock, unit, price, location, description, status, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                $data['name'],
                $data['sku'],
                $data['category_id'] ?? null,
                (int) ($data['quantity'] ?? 0),
                (int) ($data['min_stock'] ?? 5),
                $data['unit'] ?? 'шт',
                (float) ($data['price'] ?? 0),
                $data['location'] ?? null,
                $data['description'] ?? null,
                $data['status'] ?? 'active',
            ]);
            $id = (int) $this->pdo->lastInsertId();

            if (!empty($data['quantity'])) {
                $this->pdo->prepare("
                    INSERT INTO movements (item_id, type, quantity, reason, reference) VALUES (?, 'in', ?, 'Создание товара', 'NEW')
                ")->execute([$id, (int) $data['quantity']]);
            }

            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $old = $this->find($id);
        if (!$old) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE items
                SET name = ?, sku = ?, category_id = ?, min_stock = ?, unit = ?, price = ?, location = ?, description = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['sku'],
                $data['category_id'] ?? null,
                (int) ($data['min_stock'] ?? 5),
                $data['unit'] ?? 'шт',
                (float) ($data['price'] ?? 0),
                $data['location'] ?? null,
                $data['description'] ?? null,
                $data['status'] ?? 'active',
                $id,
            ]);

            $newQty = (int) ($data['quantity'] ?? $old['quantity']);
            if ($newQty !== (int) $old['quantity']) {
                $diff = $newQty - (int) $old['quantity'];
                $type = $diff > 0 ? 'in' : 'out';
                $this->pdo->prepare("
                    UPDATE items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?
                ")->execute([$newQty, $id]);
                $this->pdo->prepare("
                    INSERT INTO movements (item_id, type, quantity, reason, reference) VALUES (?, ?, ?, 'Корректировка остатка', 'ADJ')
                ")->execute([$id, $type, abs($diff)]);
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM items WHERE id = ?");
        return (bool) $stmt->execute([$id]);
    }

    public function adjustStock(int $id, int $quantity, string $type, string $reason, string $reference = ''): bool
    {
        $item = $this->find($id);
        if (!$item) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            $multiplier = $type === 'in' ? 1 : -1;
            $newQty = (int) $item['quantity'] + ($multiplier * $quantity);
            if ($newQty < 0) {
                $newQty = 0;
            }

            $this->pdo->prepare("UPDATE items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([$newQty, $id]);
            $this->pdo->prepare("
                INSERT INTO movements (item_id, type, quantity, reason, reference) VALUES (?, ?, ?, ?, ?)
            ")->execute([$id, $type, abs($quantity), $reason, $reference]);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function categories(): array
    {
        return $this->pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    }

    public function stats(): array
    {
        $totalItems = (int) $this->pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
        $lowStock = (int) $this->pdo->query("SELECT COUNT(*) FROM items WHERE quantity <= min_stock AND status = 'active'")->fetchColumn();
        $outStock = (int) $this->pdo->query("SELECT COUNT(*) FROM items WHERE quantity = 0 AND status = 'active'")->fetchColumn();
        $totalValue = (float) $this->pdo->query("SELECT COALESCE(SUM(quantity * price), 0) FROM items")->fetchColumn();
        $totalCategories = (int) $this->pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

        return [
            'total_items' => $totalItems,
            'low_stock' => $lowStock,
            'out_stock' => $outStock,
            'total_value' => $totalValue,
            'total_categories' => $totalCategories,
        ];
    }

    public function lowStock(): array
    {
        return $this->pdo->query("
            SELECT i.*, c.name AS category_name
            FROM items i
            LEFT JOIN categories c ON i.category_id = c.id
            WHERE i.quantity <= i.min_stock AND i.status = 'active'
            ORDER BY (i.quantity - i.min_stock) ASC
            LIMIT 10
        ")->fetchAll();
    }
}
