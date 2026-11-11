<?php
declare(strict_types=1);

require __DIR__ . '\bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || !$item->find($id)) {
    flash('error', 'Товар не найден');
    header('Location: items.php');
    exit;
}

$it = $item->find($id);
$movements = $movement->all(1, 10, $id)['movements'];
$categories = $item->categories();

$title = 'Товар #' . $it['id'];

require __DIR__ . '\includes\header.php';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight"><?= h($it['name']) ?></h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold
                    <?= $it['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' ?>">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    <?= $it['status'] === 'active' ? 'Активно' : 'В архиве' ?>
                </span>
                <span class="text-xs font-mono text-slate-400">SKU: <?= h($it['sku']) ?></span>
            </p>
        </div>
        <div class="flex gap-3">
            <a href="item-edit.php?id=<?= (int) $it['id'] ?>" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-200 transition-all transform active:scale-95">
                <i class="fa-solid fa-pen-to-square"></i> Редактировать
            </a>
            <a href="items.php" class="px-5 py-2.5 bg-white border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Основная информация</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div class="col-span-2">
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Название</dt>
                        <dd class="text-sm text-slate-900 dark:text-white font-medium"><?= h($it['name']) ?></dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Артикул (SKU)</dt>
                        <dd class="text-sm font-mono text-slate-900 dark:text-white"><?= h($it['sku']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Категория</dt>
                        <dd class="text-sm text-slate-900 dark:text-white inline-flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full" style="background:<?= h($it['category_color'] ?? '#6366f1') ?>"></span>
                            <?= h($it['category_name'] ?? '-') ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Единица измерения</dt>
                        <dd class="text-sm text-slate-900 dark:text-white"><?= h($it['unit']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Цена</dt>
                        <dd class="text-sm text-slate-900 dark:text-white font-medium"><?= money((float) $it['price']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Место хранения</dt>
                        <dd class="text-sm text-slate-900 dark:text-white"><?= h($it['location'] ?? '-') ?></dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Описание</dt>
                        <dd class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line"><?= h($it['description'] ?? '-') ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Минимум на складе</dt>
                        <dd class="text-sm text-slate-900 dark:text-white"><?= (int) $it['min_stock'] ?> <?= h($it['unit']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Создано</dt>
                        <dd class="text-sm text-slate-500 dark:text-slate-400 font-mono"><?= h(date('d.m.Y H:i', strtotime($it['created_at']))) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase">Обновлено</dt>
                        <dd class="text-sm text-slate-500 dark:text-slate-400 font-mono"><?= h(date('d.m.Y H:i', strtotime($it['updated_at']))) ?></dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-exchange-alt text-indigo-500"></i>
                    История движений
                </h3>
                <?php if (empty($movements)): ?>
                    <div class="text-center py-6 text-slate-400 text-sm">Истории пока нет</div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($movements as $m): ?>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/50">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    <?= $m['type'] === 'in' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : ($m['type'] === 'out' ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400') ?>">
                                    <i class="fa-solid fa-<?= $m['type'] === 'in' ? 'arrow-down' : ($m['type'] === 'out' ? 'arrow-up' : 'sliders') ?> text-xs"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white"><?= $m['type'] === 'in' ? 'Поступление' : ($m['type'] === 'out' ? 'Расход' : 'Корректировка') ?></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= h($m['reason']) ?> <?= $m['reference'] ? '· ' . h($m['reference']) : '' ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white"><?= $m['type'] === 'in' ? '+' : '-' ?> <?= abs((int) $m['quantity']) ?> <?= h($it['unit']) ?></p>
                                    <p class="text-xs text-slate-400"><?= h(date('d.m.Y H:i', strtotime($m['created_at']))) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm sticky top-24">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-5">Остаток</h3>
                <div class="text-center mb-6">
                    <div class="text-4xl font-bold text-slate-900 dark:text-white"><?= (int) $it['quantity'] ?></div>
                    <div class="text-sm text-slate-500 dark:text-slate-400"><?= h($it['unit']) ?></div>
                </div>
                <div class="space-y-3">
                    <a href="#" onclick="openAdjustModal(<?= (int) $it['id'] ?>, 'in')" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-medium shadow-md shadow-emerald-200 transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-down"></i> Поступление
                    </a>
                    <a href="#" onclick="openAdjustModal(<?= (int) $it['id'] ?>, 'out')" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-medium shadow-md shadow-rose-200 transition-colors flex items-center justify-center gap-2 mt-2">
                        <i class="fa-solid fa-arrow-up"></i> Расход
                    </a>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-500 uppercase mb-3">Общая стоимость</p>
                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400"><?= money((float) $it['quantity'] * (float) $it['price']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div id="adjustModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity opacity-0" id="adjustModalBackdrop"></div>
        <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl transition-all opacity-0 translate-y-4" id="adjustModalPanel">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white" id="adjustModalTitle">Движение товара</h3>
            </div>
            <form action="api.php?action=adjust" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="item_id" id="adjustItemId">
                <input type="hidden" name="type" id="adjustType">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Количество</label>
                    <input type="number" name="quantity" required min="1" value="1" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Причина</label>
                    <input type="text" name="reason" value="Ручное изменение" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeAdjustModal()" class="flex-1 py-3 bg-white border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700">Отмена</button>
                    <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium shadow-md shadow-indigo-200 transition-colors">Применить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAdjustModal(id, type) {
    document.getElementById('adjustItemId').value = id;
    document.getElementById('adjustType').value = type;
    document.getElementById('adjustModalTitle').textContent = type === 'in' ? 'Поступление товара' : 'Расход товара';
    const modal = document.getElementById('adjustModal');
    const backdrop = document.getElementById('adjustModalBackdrop');
    const panel = document.getElementById('adjustModalPanel');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'translate-y-4');
    });
}
function closeAdjustModal() {
    const modal = document.getElementById('adjustModal');
    const backdrop = document.getElementById('adjustModalBackdrop');
    const panel = document.getElementById('adjustModalPanel');
    backdrop.classList.add('opacity-0');
    panel.classList.add('opacity-0', 'translate-y-4');
    setTimeout(() => modal.classList.add('hidden'), 250);
}
document.getElementById('adjustModalBackdrop').addEventListener('click', closeAdjustModal);
</script>
<?php require __DIR__ . '\includes\footer.php'; ?>
