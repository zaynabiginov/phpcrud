<?php
declare(strict_types=1);

require __DIR__ . '\bootstrap.php';

$action = $_GET['action'] ?? '';

if (in_array($action, ['delete', 'adjust'], true)) {
    try {
        if ($action === 'delete') {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($id && $item->delete($id)) {
                flash('success', 'Товар удалён');
            } else {
                flash('error', 'Не удалось удалить товар');
            }
            header('Location: items.php');
            exit;
        }

        if ($action === 'adjust' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
            $type = $_POST['type'] ?? 'in';
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            $reason = $_POST['reason'] ?? 'Ручное изменение';
            $reference = $_POST['reference'] ?? '';

            if (!$itemId || !$quantity || $quantity < 1) {
                throw new Exception('Некорректные данные');
            }
            if (!in_array($type, ['in', 'out'], true)) {
                throw new Exception('Неверный тип движения');
            }

            $item->adjustStock($itemId, $quantity, $type, $reason, $reference);
            flash('success', 'Остаток обновлён');
            header('Location: item-view.php?id=' . $itemId);
            exit;
        }
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        $ref = $_GET['ref'] ?? 'items.php';
        header('Location: ' . $ref);
        exit;
    }
}

header('Content-Type: application/json; charset=utf-8');
try {
    if ($action === 'stats') {
        echo json_encode($item->stats(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } elseif ($action === 'low-stock') {
        echo json_encode($item->lowStock(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } elseif ($action === 'categories') {
        echo json_encode($item->categories(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } elseif ($action === 'item' && isset($_GET['id'])) {
        echo json_encode($item->find((int) $_GET['id']), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found'], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
