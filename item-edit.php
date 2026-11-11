<?php
declare(strict_types=1);

require __DIR__ . '\bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$it = null;
if ($id) {
    $it = $item->find($id);
    if (!$it) {
        flash('error', 'Товар не найден');
        header('Location: items.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'sku' => $_POST['sku'] ?? '',
        'category_id' => $_POST['category_id'] ?? null,
        'quantity' => $_POST['quantity'] ?? 0,
        'min_stock' => $_POST['min_stock'] ?? 5,
        'unit' => $_POST['unit'] ?? 'шт',
        'price' => $_POST['price'] ?? 0,
        'location' => $_POST['location'] ?? '',
        'description' => $_POST['description'] ?? '',
        'status' => $_POST['status'] ?? 'active',
    ];

    try {
        if ($id) {
            $item->update($id, $data);
            flash('success', 'Товар обновлён');
        } else {
            $item->create($data);
            flash('success', 'Товар создан');
        }
        header('Location: items.php');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$categories = $item->categories();
$title = $id ? 'Редактировать товар' : 'Новый товар';

require __DIR__ . '\includes\header.php';
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid <?= $id ? 'fa-pen-to-square text-indigo-500' : 'fa-plus text-blue-500' ?>"></i>
                <?= h($title) ?>
            </h2>
        </div>

        <?php if ($error): ?>
            <div class="p-6 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300">
                <p class="text-sm"><?= h($error) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="px-8 py-8 space-y-6">
            <input type="hidden" name="id" value="<?= h($id ?? '') ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Название товара <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required value="<?= h($it['name'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Артикул (SKU) <span class="text-rose-500">*</span></label>
                    <input type="text" name="sku" required value="<?= h($it['sku'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Категория</label>
                    <select name="category_id" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">Без категории</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= ($it['category_id'] ?? null) == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Единица измерения</label>
                    <input type="text" name="unit" value="<?= h($it['unit'] ?? 'шт') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Текущий остаток <span class="text-rose-500">*</span></label>
                    <input type="number" name="quantity" required value="<?= h($it['quantity'] ?? 0) ?>" min="0" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Минимум на складе <span class="text-rose-500">*</span></label>
                    <input type="number" name="min_stock" required value="<?= h($it['min_stock'] ?? 5) ?>" min="0" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Цена за единицу</label>
                    <input type="number" name="price" step="0.01" required value="<?= h($it['price'] ?? 0) ?>" min="0" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Место хранения</label>
                    <input type="text" name="location" value="<?= h($it['location'] ?? '') ?>" placeholder="Например: Стеллаж A-1" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Описание</label>
                <textarea name="description" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all"><?= h($it['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Статус</label>
                <select name="status" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="active" <?= ($it['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Активный</option>
                    <option value="archived" <?= ($it['status'] ?? '') === 'archived' ? 'selected' : '' ?>>В архиве</option>
                </select>
            </div>

            <div class="pt-6 flex justify-end gap-3">
                <a href="items.php" class="px-6 py-3 bg-white border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">Отмена</a>
                <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium shadow-md shadow-indigo-200 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Сохранить
                </button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '\includes\footer.php'; ?>
