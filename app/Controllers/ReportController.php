<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class ReportController extends Controller
{
    public function index(): void
    {
        require_role('admin');
        $pdo = Database::pdo();

        $range = $pdo->query("SELECT MIN(transaction_date) mn, MAX(transaction_date) mx FROM transactions")->fetch() ?: ['mn' => null, 'mx' => null];
        $from = $_GET['from'] ?? ($range['mn'] ?: date('Y-m-01'));
        $to = $_GET['to'] ?? ($range['mx'] ?: date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $from = $range['mn'] ?: date('Y-m-01'); }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) { $to = $range['mx'] ?: date('Y-m-d'); }
        if ($from > $to) { [$from, $to] = [$to, $from]; }

        $group = $_GET['group'] ?? 'month';
        if (!in_array($group, ['day', 'month'], true)) { $group = 'month'; }
        $flow = $_GET['flow'] ?? 'all';
        if (!in_array($flow, ['all', 'income', 'expense'], true)) { $flow = 'all'; }

        $sort = $_GET['sort'] ?? 'date';
        $dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $sortMap = ['date' => 'period', 'income' => 'income', 'expense' => 'expense', 'profit' => 'profit', 'sales_qty' => 'sales_qty', 'cost' => 'cost', 'margin' => 'margin'];
        $orderBy = $sortMap[$sort] ?? 'period';

        $periodExpr = $group === 'month' ? "DATE_FORMAT(t.transaction_date, '%Y-%m-01')" : "t.transaction_date";
        $flowWhere = '';
        $params = [$from, $to];
        if ($flow === 'income') {
            $flowWhere = " AND t.type = 'income'";
        } elseif ($flow === 'expense') {
            $flowWhere = " AND t.type = 'expense'";
        }

        $stmt = $pdo->prepare("
            SELECT $periodExpr AS period,
                   t.product_id,
                   COALESCE(SUM(CASE WHEN t.type='income' THEN t.amount ELSE 0 END), 0) AS income,
                   COALESCE(SUM(CASE WHEN t.type='income' THEN t.quantity ELSE 0 END), 0) AS in_qty,
                   COALESCE(SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END), 0) AS expense,
                   COALESCE(SUM(CASE WHEN t.type='expense' THEN t.quantity ELSE 0 END), 0) AS sales_qty
            FROM transactions t
            WHERE t.transaction_date BETWEEN ? AND ? $flowWhere
            GROUP BY period, t.product_id
        ");
        $stmt->execute($params);
        $raw = $stmt->fetchAll();

        // Overall totals for the full period (all flows) for summary section
        $overallStmt = $pdo->prepare("
            SELECT t.product_id,
                   COALESCE(SUM(CASE WHEN t.type='income' THEN t.amount ELSE 0 END), 0) AS income,
                   COALESCE(SUM(CASE WHEN t.type='income' THEN t.quantity ELSE 0 END), 0) AS in_qty,
                   COALESCE(SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END), 0) AS expense,
                   COALESCE(SUM(CASE WHEN t.type='expense' THEN t.quantity ELSE 0 END), 0) AS sales_qty
            FROM transactions t
            WHERE t.transaction_date BETWEEN ? AND ?
            GROUP BY t.product_id
        ");
        $overallStmt->execute([$from, $to]);
        $overallRaw = $overallStmt->fetchAll();

        $productIds = array_values(array_unique(array_merge(
            array_map(static fn($r) => (int)$r['product_id'], $raw),
            array_map(static fn($r) => (int)$r['product_id'], $overallRaw)
        )));
        $costMap = [];
        if ($productIds) {
            $ph = implode(',', array_fill(0, count($productIds), '?'));
            $ps = $pdo->prepare("SELECT id, cost_unit, name FROM products WHERE id IN ($ph)");
            $ps->execute($productIds);
            foreach ($ps->fetchAll() as $p) { $costMap[(int)$p['id']] = ['cost_unit' => max(0.0, (float)$p['cost_unit']), 'name' => (string)$p['name']]; }
        }

        $byPeriod = [];
        foreach ($raw as $r) {
            $p = (string) $r['period'];
            $pid = (int) $r['product_id'];
            $income = (float) $r['income'];
            $inQty = max(0.0, (float) $r['in_qty']);
            $expense = (float) $r['expense'];
            $salesQty = max(0.0, (float) $r['sales_qty']);
            $costUnit = $costMap[$pid]['cost_unit'] ?? 0.0;
            $cost = $salesQty * $costUnit;
            $profit = $expense - $cost;
            if (!isset($byPeriod[$p])) {
                $byPeriod[$p] = ['period' => $p, 'income' => 0.0, 'in_qty' => 0.0, 'expense' => 0.0, 'sales_qty' => 0.0, 'cost' => 0.0, 'profit' => 0.0, 'margin' => 0.0];
            }
            $byPeriod[$p]['income'] += $income;
            $byPeriod[$p]['in_qty'] += $inQty;
            $byPeriod[$p]['expense'] += $expense;
            $byPeriod[$p]['sales_qty'] += $salesQty;
            $byPeriod[$p]['cost'] += $cost;
            $byPeriod[$p]['profit'] += $profit;
        }
        $rows = array_values($byPeriod);
        foreach ($rows as &$rr) { $rr['margin'] = $rr['expense'] > 0 ? ($rr['profit'] / $rr['expense']) * 100.0 : 0.0; }
        unset($rr);

        usort($rows, static function ($a, $b) use ($orderBy, $dir) {
            $av = $a[$orderBy] ?? $a['period'];
            $bv = $b[$orderBy] ?? $b['period'];
            $cmp = $av <=> $bv;
            if ($cmp === 0) $cmp = $a['period'] <=> $b['period'];
            return $dir === 'ASC' ? $cmp : -$cmp;
        });

        $totalIncome = 0.0; $totalInQty = 0.0; $totalExpense = 0.0; $totalCost = 0.0; $totalProfit = 0.0; $totalSalesQty = 0.0;
        foreach ($rows as $r) {
            $totalIncome += $r['income']; $totalInQty += (float)$r['in_qty']; $totalExpense += $r['expense']; $totalCost += $r['cost']; $totalProfit += $r['profit']; $totalSalesQty += (float)$r['sales_qty'];
        }
        $totalMargin = $totalExpense > 0 ? ($totalProfit / $totalExpense) * 100.0 : 0.0;

        $overallIncome = 0.0; $overallInQty = 0.0; $overallExpense = 0.0; $overallCost = 0.0; $overallProfit = 0.0; $overallSalesQty = 0.0;
        foreach ($overallRaw as $r) {
            $pid = (int) $r['product_id'];
            $income = (float) $r['income'];
            $inQty = max(0.0, (float) $r['in_qty']);
            $expense = (float) $r['expense'];
            $salesQty = max(0.0, (float) $r['sales_qty']);
            $costUnit = $costMap[$pid]['cost_unit'] ?? 0.0;
            $cost = $salesQty * $costUnit;
            $overallIncome += $income; $overallInQty += $inQty; $overallExpense += $expense; $overallSalesQty += $salesQty; $overallCost += $cost; $overallProfit += ($expense - $cost);
        }
        $overallMargin = $overallExpense > 0 ? ($overallProfit / $overallExpense) * 100.0 : 0.0;
        $overallMarkup = $overallCost > 0 ? ($overallProfit / $overallCost) * 100.0 : 0.0;

        $baseQ = ['from' => $from, 'to' => $to, 'group' => $group, 'flow' => $flow, 'sort' => $sort, 'dir' => strtolower($dir)];
        $sortUrl = static fn(string $col): string => url('report') . '?' . http_build_query(array_merge($baseQ, ['sort' => $col, 'dir' => ($baseQ['sort'] === $col && $baseQ['dir'] === 'desc') ? 'asc' : 'desc']));
        $sortMark = static fn(string $col): string => ($sort !== $col) ? '' : ($dir === 'ASC' ? ' ▲' : ' ▼');

        $labels = array_map(static fn($r) => $group === 'month' ? substr((string) $r['period'], 0, 7) : (string) $r['period'], $rows);
        $incomeData = array_map(static fn($r) => round($r['income'], 2), $rows);
        $expenseData = array_map(static fn($r) => round($r['expense'], 2), $rows);
        $profitData = array_map(static fn($r) => round($r['profit'], 2), $rows);
        $salesQtyData = array_map(static fn($r) => (float)$r['sales_qty'], $rows);

        $this->view('report.index', [
            'title' => t('report') . ' | ' . t('app_name'),
            'active' => 'report',
            'from' => $from, 'to' => $to, 'group' => $group, 'flow' => $flow, 'sort' => $sort, 'dir' => $dir,
            'rows' => $rows,
            'totalIncome' => $totalIncome, 'totalInQty' => $totalInQty, 'totalExpense' => $totalExpense,
            'totalCost' => $totalCost, 'totalProfit' => $totalProfit, 'totalSalesQty' => $totalSalesQty, 'totalMargin' => $totalMargin,
            'overallIncome' => $overallIncome, 'overallInQty' => $overallInQty, 'overallExpense' => $overallExpense,
            'overallCost' => $overallCost, 'overallProfit' => $overallProfit, 'overallSalesQty' => $overallSalesQty,
            'overallMargin' => $overallMargin, 'overallMarkup' => $overallMarkup,
            'sortUrl' => $sortUrl, 'sortMark' => $sortMark,
            'labels' => $labels, 'incomeData' => $incomeData, 'expenseData' => $expenseData, 'profitData' => $profitData, 'salesQtyData' => $salesQtyData,
            'profitClass' => $totalProfit >= 0 ? 'kpi-income' : 'kpi-expense',
        ]);
    }
}