<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class StockController extends Controller
{
    public function index(): void
    {
        require_login();
        $pdo = Database::pdo();
        $saveMsg = '';
        $addMsg = '';
        $addMsgType = 'success';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['delete_id'])) {
            require_role('admin');
            $pid = (int) $_POST['delete_id'];
            if ($pid > 0) {
                $pdo->beginTransaction();
                try {
                    $pdo->prepare("DELETE FROM transactions WHERE product_id = ?")->execute([$pid]);
                    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$pid]);
                    $pdo->commit();
                    $saveMsg = t('deleted');
                } catch (\Throwable $e) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    $saveMsg = t('delete_error') . $e->getMessage();
                }
            }
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['save_id'])) {
            require_role('admin');
            $pid = (int) $_POST['save_id'];
            $name = trim((string) ($_POST['product'][$pid] ?? ''));
            $description = trim((string) ($_POST['description'][$pid] ?? ''));
            $unit = normalize_unit($_POST['unit'][$pid] ?? 'шт');
            $qtyInTarget = parse_qty($_POST['qty_in'][$pid] ?? 0);
            $qtyOutTarget = parse_qty($_POST['qty_out'][$pid] ?? 0);
            if ($qtyOutTarget > $qtyInTarget) $qtyOutTarget = $qtyInTarget;
            $costUnit = max(0.0, parse_money($_POST['cost_unit'][$pid] ?? 0));
            $markupPct = max(0.0, parse_money($_POST['markup_pct'][$pid] ?? 0));
            $saleUnit = $costUnit * (100.0 + $markupPct) / 100.0;
            $date = date('Y-m-d');

            $pdo->beginTransaction();
            try {
                if ($name !== '') {
                    $pdo->prepare("UPDATE products SET name = ?, description = ?, unit = ? WHERE id = ?")->execute([$name, $description !== '' ? $description : null, $unit, $pid]);
                } else {
                    $pdo->prepare("UPDATE products SET description = ?, unit = ? WHERE id = ?")->execute([$description !== '' ? $description : null, $unit, $pid]);
                }
                $pdo->prepare("UPDATE products SET cost_unit = ?, markup_pct = ? WHERE id = ?")->execute([$costUnit, $markupPct, $pid]);

                $cur = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM transactions WHERE product_id = ? AND type = 'expense'");
                $cur->execute([$pid]);
                $actualQtyOut = round((float) $cur->fetchColumn(), 3);

                $pdo->prepare("DELETE FROM transactions WHERE product_id = ? AND type = 'income'")->execute([$pid]);
                $ins = $pdo->prepare("INSERT INTO transactions (type, product_id, user_id, quantity, amount, transaction_date) VALUES (?, ?, ?, ?, ?, ?)");
                $uid = (int) (current_user()['id'] ?? 0);
                $uid = $uid > 0 ? $uid : null;
                if ($qtyInTarget > 0) {
                    $ins->execute(['income', $pid, $uid, $qtyInTarget, round($qtyInTarget * $costUnit, 2), $date]);
                }

                if (abs($qtyOutTarget - $actualQtyOut) > 0.0005) {
                    $pdo->prepare("DELETE FROM transactions WHERE product_id = ? AND type = 'expense'")->execute([$pid]);
                    if ($qtyOutTarget > 0) {
                        $ins->execute(['expense', $pid, $uid, $qtyOutTarget, round($qtyOutTarget * $saleUnit, 2), $date]);
                    }
                }

                $pdo->commit();
                $saveMsg = t('saved') . ($name !== '' ? $name : ('#' . $pid));
            } catch (\Throwable $e) {
                $pdo->rollBack();
                $saveMsg = t('save_error') . $e->getMessage();
            }
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['add_stock'])) {
            if (!can_add_stock()) { require_role('admin'); }
            $qtyIn = parse_qty($_POST['add_qty'] ?? 0);
            $unit = normalize_unit($_POST['add_unit'] ?? 'шт');
            $costUnit = max(0.0, parse_money($_POST['add_cost_unit'] ?? 0));
            $markupPct = max(0.0, parse_money($_POST['add_markup_pct'] ?? 100));
            $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_POST['add_date'] ?? '')) ? (string) $_POST['add_date'] : date('Y-m-d');
            $newName = trim((string) ($_POST['add_new_name'] ?? ''));
            $newDesc = trim((string) ($_POST['add_description'] ?? ''));
            $productId = (int) ($_POST['add_product_id'] ?? 0);

            if ($qtyIn <= 0 || ($productId <= 0 && $newName === '')) {
                $addMsg = t('specify_product_qty');
                $addMsgType = 'error';
            } elseif ($productId > 0 && $newName !== '') {
                $addMsg = t('choose_existing_or_new');
                $addMsgType = 'error';
            } else {
                $pdo->beginTransaction();
                try {
                    $created = false;
                    if ($productId <= 0) {
                        $insP = $pdo->prepare("INSERT INTO products (name, description, unit, cost_unit, markup_pct) VALUES (?, ?, ?, ?, ?)");
                        $insP->execute([$newName, $newDesc !== '' ? $newDesc : null, $unit, $costUnit, $markupPct]);
                        $productId = (int) $pdo->lastInsertId();
                        $created = true;
                    } else {
                        $p = $pdo->prepare("SELECT id, name, unit, cost_unit, markup_pct FROM products WHERE id = ?");
                        $p->execute([$productId]);
                        $prod = $p->fetch();
                        if (!$prod) throw new \RuntimeException(t('product_not_found'));
                        $newName = (string) $prod['name'];
                        $unit = normalize_unit($prod['unit'] ?? $unit);
                        if ($costUnit <= 0.0) $costUnit = max(0.0, (float) $prod['cost_unit']);
                        $pdo->prepare("UPDATE products SET cost_unit = ?, markup_pct = ?, unit = ? WHERE id = ?")->execute([$costUnit, $markupPct, $unit, $productId]);
                        if ($newDesc !== '') {
                            $pdo->prepare("UPDATE products SET description = ? WHERE id = ?")->execute([$newDesc, $productId]);
                        }
                    }
                    $amount = round($qtyIn * $costUnit, 2);
                    $uid = (int) (current_user()['id'] ?? 0);
                    $uid = $uid > 0 ? $uid : null;
                    $insT = $pdo->prepare("INSERT INTO transactions (type, product_id, user_id, quantity, amount, transaction_date) VALUES ('income', ?, ?, ?, ?, ?)");
                    $insT->execute([$productId, $uid, $qtyIn, $amount, $date]);
                    $pdo->commit();
                    $addMsg = ($created ? t('product_created_stocked') : t('stock_added')) . $newName . ' (+' . fmt_qty($qtyIn) . ' ' . unit_label($unit) . ')';
                    $addMsgType = 'success';
                } catch (\Throwable $e) {
                    $pdo->rollBack();
                    $addMsg = t('add_error') . $e->getMessage();
                    $addMsgType = 'error';
                }
            }
        }

        if (isset($_GET['sold'])) {
            $addMsg = t('sale_done_stock_updated');
            $addMsgType = 'success';
        }

        $allProducts = $pdo->query("SELECT id, name, description, unit, cost_unit, markup_pct FROM products ORDER BY name")->fetchAll();

        $sort = ($_GET['sort'] ?? 'name') === 'date' ? 'date' : 'name';
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $orderSql = $sort === 'date'
            ? "(MAX(t.transaction_date) IS NULL) ASC, MAX(t.transaction_date) $dir, p.name ASC"
            : "p.name $dir, (MAX(t.transaction_date) IS NULL) ASC, MAX(t.transaction_date) DESC";

        $stmt = $pdo->query("
            SELECT p.id, p.name AS product, p.description, p.unit,
                   p.cost_unit, p.markup_pct,
                   COALESCE(SUM(CASE WHEN t.type='income' THEN t.quantity ELSE 0 END), 0) AS qty_in,
                   COALESCE(SUM(CASE WHEN t.type='expense' THEN t.quantity ELSE 0 END), 0) AS qty_out,
                   MAX(t.transaction_date) AS last_date
            FROM products p
            LEFT JOIN transactions t ON t.product_id = p.id
            GROUP BY p.id, p.name, p.description, p.unit, p.cost_unit, p.markup_pct
            ORDER BY $orderSql
        ");
        $rows = $stmt->fetchAll();

        $tot = ['cost_stock' => 0.0, 'sale_stock' => 0.0, 'positions' => 0, 'items' => 0];
        foreach ($rows as &$r) {
            $r['unit'] = normalize_unit($r['unit'] ?? 'шт');
            $r['qty_in'] = max(0.0, (float) $r['qty_in']);
            $r['qty_out'] = min($r['qty_in'], max(0.0, (float) $r['qty_out']));
            $r['qty_stock'] = round($r['qty_in'] - $r['qty_out'], 3);
            $r['cost_unit'] = max(0.0, (float) $r['cost_unit']);
            $r['markup_pct'] = max(0.0, (float) $r['markup_pct']);
            $r['sale_unit'] = $r['cost_unit'] * (100.0 + $r['markup_pct']) / 100.0;
            $r['cost_stock'] = $r['qty_stock'] * $r['cost_unit'];
            $r['sale_stock'] = $r['qty_stock'] * $r['sale_unit'];
            $tot['cost_stock'] += $r['cost_stock'];
            $tot['sale_stock'] += $r['sale_stock'];
            $tot['items']++;
            if ($r['qty_stock'] > 0) $tot['positions']++;
        }
        unset($r);

        $this->view('stock.index', [
            'title' => t('warehouse') . ' | ' . t('app_name'),
            'active' => 'stock',
            'saveMsg' => $saveMsg,
            'addMsg' => $addMsg,
            'addMsgType' => $addMsgType,
            'allProducts' => $allProducts,
            'rows' => $rows,
            'tot' => $tot,
            'isAdmin' => can_edit_stock(),
            'canAddStock' => can_add_stock(),
            'allowedUnits' => allowed_units(),
            'sort' => $sort,
            'dir' => strtolower($dir),
        ]);
    }
}