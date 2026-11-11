<?php
declare(strict_types=1);

require __DIR__ . '\bootstrap.php';
Database::init();

$stats = $item->stats();
$lowStock = $item->lowStock();
$recentMovements = $movement->all(1, 5)['movements'];
$categories = $item->categories();

$title = 'Панель управления';

require __DIR__ . '\includes\header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Складской учёт</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Управление товарами, остатками и движениями на складе</p>
        </div>
        <a href="items.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all transform active:scale-95">
            <i class="fa-solid fa-boxes-stacked"></i>
            Товары
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Всего товаров</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1"><?= h($stats['total_items']) ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i class="fa-solid fa-box text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Низкий остаток</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1 text-rose-500"><?= h($stats['low_stock']) ?></p>
                </div>
                <div class="w-12 h-12 bg-rose-50 dark:bg-rose-900/20 rounded-xl flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Нет в наличии</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1 text-rose-600"><?= h($stats['out_stock']) ?></p>
                </div>
                <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/30 rounded-xl flex items-center justify-center text-rose-500 dark:text-rose-400">
                    <i class="fa-solid fa-box-empty text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Категорий</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1 text-indigo-600"><?= h($stats['total_categories']) ?></p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <i class="fa-solid fa-layer-group text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Общая стоимость</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1 text-emerald-600"><?= money($stats['total_value']) ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-money-bill-wave text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i>
                Недавние движения
            </h3>
            <div class="overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                            <th class="pb-3 font-medium">Товар</th>
                            <th class="pb-3 font-medium">Тип</th>
                            <th class="pb-3 font-medium">Количество</th>
                            <th class="pb-3 font-medium">Причина</th>
                            <th class="pb-3 font-medium text-right">Дата</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        <?php foreach ($recentMovements as $m): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="py-3 text-slate-900 dark:text-white font-medium"><?= h($m['item_name']) ?> (<?= h($m['sku']) ?>)</td>
                                <td class="py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                        <?= $m['type'] === 'in' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : ($m['type'] === 'out' ? 'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400') ?>">
                                        <i class="fa-solid fa-<?= $m['type'] === 'in' ? 'arrow-down' : ($m['type'] === 'out' ? 'arrow-up' : 'sliders') ?>"></i>
                                        <?= $m['type'] === 'in' ? 'Поступление' : ($m['type'] === 'out' ? 'Расход' : 'Корректировка') ?>
                                    </span>
                                </td>
                                <td class="py-3 text-slate-900 dark:text-white font-mono"><?= h(abs((int) $m['quantity'])) ?></td>
                                <td class="py-3 text-slate-500 dark:text-slate-400"><?= h($m['reason']) ?></td>
                                <td class="py-3 text-right text-slate-400 text-xs"><?= h(date('d.m.Y H:i', strtotime($m['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentMovements)): ?>
                            <tr><td colspan="5" class="py-8 text-center text-slate-400 text-sm">Движений пока нет</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                Требуют пополнения
            </h3>
            <?php if (empty($lowStock)): ?>
                <div class="text-center py-10 text-slate-400 text-sm">
                    <i class="fa-solid fa-circle-check text-2xl mb-3 block text-emerald-400"></i>
                    Все товары в наличии
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($lowStock as $ls): ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100/30 dark:border-amber-900/20">
                            <div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white"><?= h($ls['name']) ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?= h($ls['sku']) ?> · <?= h($ls['location'] ?? '-') ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-rose-600 dark:text-rose-400"><?= (int) $ls['quantity'] ?> <?= h($ls['unit']) ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">минимум <?= (int) $ls['min_stock'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '\includes\footer.php'; ?>
