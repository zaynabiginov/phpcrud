<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class HomeController extends Controller
{
    public function index(): void
    {
        require_login();
        $pdo = Database::pdo();

        $type = trim((string) ($_GET['type'] ?? 'all'));
        if (!in_array($type, ['all', 'income', 'expense'], true)) {
            $type = 'all';
        }
        $perPageParam = (string) ($_GET['per_page'] ?? '30');
        $allowedPerPage = ['30', '50', '70', 'all'];
        if (!in_array($perPageParam, $allowedPerPage, true)) {
            $perPageParam = '30';
        }
        $search = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $conds = [];
        $params = [];
        if ($type !== 'all') {
            $conds[] = 't.type = ?';
            $params[] = $type;
        }
        if ($search !== '') {
            $conds[] = 'p.name LIKE ?';
            $params[] = '%' . $search . '%';
        }
        $where = $conds ? ('WHERE ' . implode(' AND ', $conds)) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t JOIN products p ON p.id = t.product_id $where");
        $countStmt->execute($params);
        $totalOps = (int) $countStmt->fetchColumn();

        if ($perPageParam === 'all') {
            $perPage = $totalOps > 0 ? $totalOps : 1;
            $totalPages = 1;
            $page = 1;
            $offset = 0;
            $limitSql = '';
        } else {
            $perPage = (int) $perPageParam;
            $totalPages = max(1, (int) ceil($totalOps / $perPage));
            if ($page > $totalPages) {
                $page = $totalPages;
            }
            $offset = ($page - 1) * $perPage;
            $limitSql = "LIMIT $perPage OFFSET $offset";
        }

        $sql = "
            SELECT t.id, t.type, t.quantity, t.amount, t.transaction_date, p.name AS product, p.unit, u.username AS performed_by
            FROM transactions t
            JOIN products p ON p.id = t.product_id
            LEFT JOIN users u ON u.id = t.user_id
            $where
            ORDER BY t.transaction_date DESC, t.id DESC
            $limitSql
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $recent = $stmt->fetchAll();

        $stats = $pdo->query("
            SELECT
                COUNT(*) AS total_products,
                COALESCE(SUM(CASE WHEN stock_qty > 0 THEN 1 ELSE 0 END), 0) AS in_stock,
                COALESCE(SUM(CASE WHEN stock_qty <= 0 THEN 1 ELSE 0 END), 0) AS out_stock,
                COALESCE(SUM(CASE WHEN stock_qty > 0 AND stock_qty <= 5 THEN 1 ELSE 0 END), 0) AS low_stock
            FROM (
                SELECT p.id,
                       COALESCE(SUM(CASE WHEN t.type='income' THEN t.quantity ELSE -t.quantity END), 0) AS stock_qty
                FROM products p
                LEFT JOIN transactions t ON t.product_id = p.id
                GROUP BY p.id
            ) s
        ")->fetch() ?: ['total_products'=>0,'in_stock'=>0,'out_stock'=>0,'low_stock'=>0];

        $today = date('Y-m-d');
        $tstmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount),0) AS revenue, COUNT(*) AS sales
            FROM transactions
            WHERE type='expense' AND transaction_date = ?
        ");
        $tstmt->execute([$today]);
        $todayStats = $tstmt->fetch() ?: ['revenue'=>0,'sales'=>0];

        $message = ($_GET['err'] ?? '') === 'forbidden' ? t('access_denied') : '';
        $this->view('home.index', [
            'title' => t('home') . ' | ' . t('app_name'),
            'active' => 'index',
            'recent' => $recent,
            'message' => $message,
            'stats' => $stats,
            'todayStats' => $todayStats,
            'today' => $today,
            'filterType' => $type,
            'searchQuery' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'perPageParam' => $perPageParam,
            'totalOps' => $totalOps,
            'totalPages' => $totalPages,
        ]);
    }
}