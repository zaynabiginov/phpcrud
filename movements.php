<?php
declare(strict_types=1);

require __DIR__ . '\bootstrap.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$itemId = filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT);
$type = $_GET['type'] ?? '';

$result = $movement->all($page, 25, $itemId, $type ?: null);
$items = $item->all()['items'];

$title = 'История движений';

require __DIR__ . '\includes\header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">История движений</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Журнал всех приходов, расходов и корректировок остатков</p>
        </div>
        <a href="index.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left"></i> Назад
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Товар</label>
                    <select name="item_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">Все товары</option>
                        <?php foreach ($items as $i): ?>
                            <option value="<?= (int) $i['id'] ?>" <?= $itemId == $i['id'] ? 'selected' : '' ?>><?= h($i['name']) ?> (<?= h($i['sku']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Тип</label>
                    <select name="type" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">Все типы</option>
                        <option value="in" <?= $type === 'in' ? 'selected' : '' ?>>Поступление</option>
                        <option value="out" <?= $type === 'out' ? 'selected' : '' ?>>Расход</option>
                        <option value="adjust" <?= $type === 'adjust' ? 'selected' : '' ?>>Корректировка</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button class="w-full px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors shadow-sm shadow-indigo-200">Показать</button>
                </div>
                <div class="md:col-span-3 flex items-end gap-2">
                    <a href="movements.php" class="px-5 py-2.5 bg-white border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Сброс</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Товар</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Количество</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Причина</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Референс</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                    <?php foreach ($result['movements'] as $m): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 text-sm font-mono text-slate-600 dark:text-slate-300"><?= h(date('d.m.Y H:i', strtotime($m['created_at']))) ?></td>
                            <td class="px-6 py-4 text-sm text-slate-900 dark:text-white font-medium truncate-word max-w-xs"><?= h($m['item_name']) ?> <span class="text-slate-500 dark:text-slate-500">(<?= h($m['sku']) ?>)</span></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                                    <?= $m['type'] === 'in' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : ($m['type'] === 'out' ? 'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400') ?>">
                                    <i class="fa-solid fa-<?= $m['type'] === 'in' ? 'arrow-down' : ($m['type'] === 'out' ? 'arrow-up' : 'sliders') ?>"></i>
                                    <?= $m['type'] === 'in' ? 'Поступление' : ($m['type'] === 'out' ? 'Расход' : 'Корректировка') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-slate-900 dark:text-white">
                                <?= $m['type'] === 'in' ? '+' : '-' ?> <?= abs((int) $m['quantity']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300"><?= h($m['reason']) ?></td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono"><?= h($m['reference'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($result['movements'])): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">Записей не найдено</td></tr>
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
                            <a href="?item_id=<?= h($itemId ?? '') ?>&type=<?= h($type) ?>&page=<?= $result['page'] - 1 ?>" class="px-3 py-2 bg-white border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Назад</a>
                        <?php else: ?>
                            <span class="px-3 py-2 rounded-lg text-sm text-slate-400 bg-slate-100 dark:bg-slate-700">Назад</span>
                        <?php endif; ?>
                        <span class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600">
                            <?= h($result['page']) ?> / <?= h($result['pages']) ?>
                        </span>
                        <?php if ($result['page'] < $result['pages']): ?>
                            <a href="?item_id=<?= h($itemId ?? '') ?>&type=<?= h($type) ?>&page=<?= $result['page'] + 1 ?>" class="px-3 py-2 bg-white border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Вперёд</a>
                        <?php else: ?>
                            <span class="px-3 py-2 rounded-lg text-sm text-slate-400 bg-slate-100 dark:bg-slate-700">Вперёд</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '\includes\footer.php'; ?>
