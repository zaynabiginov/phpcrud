<?php
declare(strict_types=1);

class Movement
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(int $page = 1, int $perPage = 20, ?int $itemId = null, ?string $type = null): array
    {
        $sql = "SELECT m.*, i.name AS item_name, i.sku
                FROM movements m
                JOIN items i ON m.item_id = i.id
                WHERE 1=1";
        $params = [];

        if ($itemId !== null) {
            $sql .= " AND m.item_id = ?";
            $params[] = $itemId;
        }
        if ($type !== null && $type !== '') {
            $sql .= " AND m.type = ?";
            $params[] = $type;
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ({$sql})");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql .= " ORDER BY m.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return ['movements' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => (int) ceil($total / $perPage)];
    }
}
