<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;

final class SellController extends Controller
{
    public function index(): void
    {
        require_login();
        $message = '';
        $messageType = 'success';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $productId = (int) ($_POST['product_id'] ?? 0);
            $quantity = parse_qty($_POST['quantity'] ?? '0');
            $date = (string) ($_POST['date'] ?? '');
            $amount = parse_money($_POST['amount'] ?? '0');

            if (!$productId || $quantity <= 0 || $date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $message = t('fill_fields_correctly');
                $messageType = 'error';
            } else {
                $prod = Product::findForSale($productId);
                if (!$prod) {
                    $message = t('product_not_found');
                    $messageType = 'error';
                } else {
                    $unit = normalize_unit($prod['unit'] ?? 'шт');
                    $stockQty = round(max(0.0, (float) $prod['stock_qty']), 3);
                    if ($quantity - $stockQty > 0.0005) {
                        $message = t('not_enough_stock') . fmt_qty($stockQty) . ' ' . unit_label($unit) . '.';
                        $messageType = 'error';
                    } else {
                        $cost = max(0.0, (float) $prod['cost_unit']);
                        $markup = max(0.0, (float) $prod['markup_pct']);
                        $salePrice = round($cost * (100.0 + $markup) / 100.0, 2);
                        $calcAmount = round($salePrice * $quantity, 2);
                        $minAmount = round($calcAmount * 0.15, 2);
                        if ($amount <= 0) $amount = $calcAmount;
                        if ($amount + 0.001 < $minAmount) {
                            $message = sprintf(t('amount_below_min'), fmt_money($minAmount));
                            $messageType = 'error';
                        } elseif ($amount - 0.001 > $calcAmount) {
                            $message = sprintf(t('amount_above_max'), fmt_money($calcAmount));
                            $messageType = 'error';
                        } else {
                            $pdo = Database::pdo();
                            $userId = (int) (current_user()['id'] ?? 0);
                            $userId = $userId > 0 ? $userId : null;
                            $stmt = $pdo->prepare("INSERT INTO transactions (type, product_id, user_id, quantity, amount, transaction_date) VALUES ('expense', ?, ?, ?, ?, ?)");
                            $stmt->execute([$productId, $userId, $quantity, $amount, $date]);
                            $this->redirect(url('warehouse?sold=1'));
                        }
                    }
                }
            }
        }

        if (isset($_GET['sold'])) {
            $message = t('sale_done_stock_updated');
            $messageType = 'success';
        }

        $this->view('sell.index', [
            'title' => t('create_sale_title') . ' | ' . t('app_name'),
            'active' => 'sell',
            'message' => $message,
            'messageType' => $messageType,
            'old' => [
                'product_id' => (int) ($_POST['product_id'] ?? 0),
                'quantity' => (string) ($_POST['quantity'] ?? '1'),
                'amount' => (string) ($_POST['amount'] ?? ''),
                'date' => (string) ($_POST['date'] ?? date('Y-m-d')),
            ],
        ]);
    }
}