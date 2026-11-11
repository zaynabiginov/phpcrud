<?php
declare(strict_types=1);

require __DIR__ . '\bootstrap.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = $item->all($search ?: null, $category ?: null, $status ?: null, $page);
$categories = $item->categories();

$title = 'Товары на складе';

require __DIR__ . '\includes\header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Товары</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Управление остатками и карточками товаров</p>
        </div>
        <a href="item-create.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all transform active:scale-95">
            <i class="fa-solid fa-plus"></i>
            Добавить товар
        </a>
    </div>

    <?php $f = flash('success'); if ($f): ?>
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center gap-3">
            <i class="fa-solid fa-circle-check"></i>
            <p class="text-sm font-medium"><?= h($f) ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Поиск</label>
                    <input type="search" name="search" value="<?= h($search) ?>" placeholder="Название, артикул, место" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Категория</label>
                    <select name="category" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">Все категории</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= $category == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Статус</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">Все статусы</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Активные</option>
                        <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>В архиве</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button class="w-full px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors shadow-sm shadow-indigo-200">Показать</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Товар</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Артикул</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Категория</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Остаток</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden xl:table-cell">Цена</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Место</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                    <?php foreach ($result['items'] as $it): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                                        <?= strtoupper(substr(h($it['name']), 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900 dark:text-white"><?= h($it['name']) ?></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400"><?= h($it['description'] ?? '-') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono"><?= h($it['sku']) ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 hidden md:table-cell">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium border border-transparent" style="border-color:<?= h($it['category_color'] ?? '#6366f1') ?>">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background:<?= h($it['category_color'] ?? '#6366f1') ?>"></span>
                                    <?= h($it['category_name'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium hidden lg:table-cell">
                                <span class="px-2 py-1 rounded-full text-xs font-bold
                                    <?= (int) $it['quantity'] === 0 ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400' : ((int) $it['quantity'] <= (int) $it['min_stock'] ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400') ?>">
                                    <?= (int) $it['quantity'] ?> <?= h($it['unit']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 hidden xl:table-cell"><?= money((float) $it['price']) ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300"><?= h($it['location'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="item-view.php?id=<?= (int) $it['id'] ?>" class="p-2 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-lg transition-colors" title="Просмотр">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="item-edit.php?id=<?= (int) $it['id'] ?>" class="p-2 text-blue-600 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors" title="Редактировать">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= (int) $it['id'] ?>, '<?= h($it['name']) ?>')" class="p-2 text-rose-600 hover:bg-rose-50 hover:text-rose-700 rounded-lg transition-colors" title="Удалить">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($result['items'])): ?>
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">Товары не найдены</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($result['pages'] > 1): ?>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        С <?= h((( $result['page'] - 1) * $result['perPage']) + 1) ?> по <?= h(min($result['page'] * $result['perPage'], $result['total'])) ?> из <?= h($result['total']) ?>
                    </div>
                    <div class="flex gap-1">
                        <?php if ($result['page'] > 1): ?>
                            <a href="?search=<?= urlencode($search) ?>&category=<?= h($category) ?>&status=<?= h($status) ?>&page=<?= $result['page'] - 1 ?>" class="px-3 py-2 bg-white border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Назад</a>
                        <?php else: ?>
                            <span class="px-3 py-2 rounded-lg text-sm text-slate-400 bg-slate-100 dark:bg-slate-700">Назад</span>
                        <?php endif; ?>
                        <span class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600">
                            <?= h($result['page']) ?> / <?= h($result['pages']) ?>
                        </span>
                        <?php if ($result['page'] < $result['pages']): ?>
                            <a href="?search=<?= urlencode($search) ?>&category=<?= h($category) ?>&status=<?= h($status) ?>&page=<?= $result['page'] + 1 ?>" class="px-3 py-2 bg-white border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Вперёд</a>
                        <?php else: ?>
                            <span class="px-3 py-2 rounded-lg text-sm text-slate-400 bg-slate-100 dark:bg-slate-700">Вперёд</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    if (confirm('Удалить товар "' + name + '"? Все связанные движения также будут удалены.')) {
        location.href = 'api.php?action=delete&id=' + id;
    }
}
</script>
<?php require __DIR__ . '\includes\footer.php'; ?>
