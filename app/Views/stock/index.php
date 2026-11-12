<?php
$sort = $sort ?? 'name';
$dir = $dir ?? 'asc';
$sortUrl = static function (string $col) use ($sort, $dir): string {
    $nextDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
    return url('warehouse') . '?sort=' . $col . '&dir=' . $nextDir;
};
$sortMark = static function (string $col) use ($sort, $dir): string {
    if ($sort !== $col) return '';
    return $dir === 'asc' ? ' ▲' : ' ▼';
};
?>
<?php if ($saveMsg !== ''): ?>
    <div class="save-note"><?= e(db_text($saveMsg)) ?></div>
<?php endif; ?>
<?php if ($addMsg !== ''): ?>
    <div class="save-note" style="color:<?= $addMsgType === 'error' ? 'var(--expense)' : 'var(--income)' ?>;"><?= e(db_text($addMsg)) ?></div>
<?php endif; ?>

<div class="kpi-grid">
    <div class="card kpi kpi-count"><span class="kpi-label"><?= e(t('positions_total')) ?></span><span class="kpi-value"><?= $tot['items'] ?></span><span class="kpi-sub"><?= e(t('in_catalog')) ?></span></div>
    <div class="card kpi kpi-balance"><span class="kpi-label"><?= e(t('positions_in_stock')) ?></span><span class="kpi-value"><?= $tot['positions'] ?></span><span class="kpi-sub"><?= e(t('stock_gt_zero')) ?></span></div>
    <div class="card kpi kpi-income"><span class="kpi-label"><?= e(t('cost_stock_total')) ?></span><span class="kpi-value"><?= fmt_money($tot['cost_stock']) ?></span><span class="kpi-sub"><?= e(t('currency')) ?></span></div>
    <div class="card kpi kpi-balance"><span class="kpi-label"><?= e(t('sale_stock_total')) ?></span><span class="kpi-value"><?= fmt_money($tot['sale_stock']) ?></span><span class="kpi-sub"><?= e(t('currency')) ?></span></div>
</div>

<section class="card">
    <div class="stock-head">
        <h2 style="margin:0;"><?= e(t('stock_items')) ?></h2>
        <div class="stock-toolbar">
            <input type="search" id="stockSearch" class="stock-search" placeholder="<?= e(t('search')) ?>..." aria-label="<?= e(t('search')) ?>" autocomplete="off">
            <?php if ($canAddStock): ?>
            <button type="button" class="btn btn-primary" id="btnAddStock">+ <?= e(t('add_to_stock')) ?></button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th><a href="<?= e($sortUrl('date')) ?>" style="color:inherit; text-decoration:none;"><?= e(t('last_op_date')) ?><?= $sortMark('date') ?></a></th>
                    <th><a href="<?= e($sortUrl('name')) ?>" style="color:inherit; text-decoration:none;"><?= e(t('product')) ?><?= $sortMark('name') ?></a></th>
                    <th><?= e(t('description')) ?></th>
                    <th><?= e(t('unit')) ?></th>
                    <th><?= e(t('received')) ?></th>
                    <th><?= e(t('qty_sold')) ?></th>
                    <th><?= e(t('stock_qty')) ?></th>
                    <th><?= e(t('cost_per_unit')) ?></th>
                    <th><?= e(t('markup_pct')) ?></th>
                    <th><?= e(t('sale_price_per_unit')) ?></th>
                    <th><?= e(t('cost_stock')) ?></th>
                    <th><?= e(t('sale_stock')) ?></th>
                    <?php if ($isAdmin): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows): foreach ($rows as $row): $pid = (int) $row['id']; $desc = trim((string) ($row['description'] ?? '')); $u = $row['unit']; $ul = unit_label($u); ?>
                <tr class="stock-row">
                    <td><?= e((string) ($row['last_date'] ?? '—')) ?></td>
                    <td><?= e(db_text($row['product'])) ?></td>
                    <td class="desc-cell"><?= $desc !== '' ? nl2br(e(db_text($desc))) : '—' ?></td>
                    <td><?= e($ul) ?></td>
                    <td><?= fmt_qty($row['qty_in']) . ' ' . e($ul) ?></td>
                    <td><?= fmt_qty($row['qty_out']) . ' ' . e($ul) ?></td>
                    <td style="font-weight:700; color:var(--income);"><?= fmt_qty($row['qty_stock']) . ' ' . e($ul) ?></td>
                    <td><?= fmt_money($row['cost_unit']) ?></td>
                    <td><?= rtrim(rtrim(number_format((float)$row['markup_pct'], 2, ',', ' '), '0'), ',') ?>%</td>
                    <td><?= fmt_money($row['sale_unit']) ?></td>
                    <td><?= fmt_money($row['cost_stock']) ?></td>
                    <td style="font-weight:700; color:var(--income);"><?= fmt_money($row['sale_stock']) ?></td>
                    <?php if ($isAdmin): ?>
                    <td>
                        <div class="action-btns">
                            <button type="button" class="btn-icon btn-icon-edit btn-edit" title="<?= e(t('edit')) ?>" aria-label="<?= e(t('edit')) ?>"
                                data-id="<?= $pid ?>"
                                data-product="<?= e(db_text($row['product'])) ?>"
                                data-description="<?= e(db_text($desc)) ?>"
                                data-unit="<?= e($u) ?>"
                                data-qty-in="<?= e((string) $row['qty_in']) ?>"
                                data-qty-out="<?= e((string) $row['qty_out']) ?>"
                                data-cost-unit="<?= e((string) $row['cost_unit']) ?>"
                                data-markup-pct="<?= e((string) $row['markup_pct']) ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            </button>
                            <form method="POST" action="<?= url('warehouse') ?>" class="inline-form" onsubmit="return confirm(<?= e(json_encode(sprintf(t('delete_product_confirm'), db_text($row['product'])))) ?>);">
                                <input type="hidden" name="delete_id" value="<?= $pid ?>">
                                <button type="submit" class="btn-icon btn-icon-danger" title="<?= e(t('delete')) ?>" aria-label="<?= e(t('delete')) ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <tr class="stock-total-row" style="font-weight:700; border-top:2px solid var(--border);">
                    <td><?= e(t('total')) ?></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td><?= fmt_money($tot['cost_stock']) ?> <?= e(t('currency')) ?></td>
                    <td style="color:var(--income);"><?= fmt_money($tot['sale_stock']) ?> <?= e(t('currency')) ?></td><?php if ($isAdmin): ?><td></td><?php endif; ?>
                </tr>
                <?php else: ?>
                <tr class="stock-empty-row"><td colspan="<?= $isAdmin ? 13 : 12 ?>" style="text-align:center; color: var(--muted); padding: 2rem;"><?= e(t('no_products_stock')) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($canAddStock): ?>
<div class="modal-backdrop<?= $addMsgType === 'error' && $addMsg !== '' ? ' open' : '' ?>" id="addModal" aria-hidden="<?= $addMsgType === 'error' && $addMsg !== '' ? 'false' : 'true' ?>">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
        <form method="POST" action="<?= url('warehouse') ?>" id="addStockForm" novalidate>
            <div class="modal-head">
                <h3 id="addModalTitle"><?= e(t('add_product_title')) ?></h3>
                <button type="button" class="modal-close" id="addModalClose" aria-label="<?= e(t('close')) ?>">×</button>
            </div>
            <div class="modal-body">
                <p style="color:var(--muted); margin:0 0 1rem; font-size:.9rem;"><?= e(t('add_product_hint')) ?></p>
                <input type="hidden" name="add_stock" value="1">
                <div class="modal-grid">
                    <div class="full">
                        <label for="addProduct"><?= e(t('existing_or_new')) ?></label>
                        <select name="add_product_id" id="addProduct">
                            <option value=""><?= e(t('new_product')) ?></option>
                            <?php foreach ($allProducts as $ap): $u = normalize_unit($ap['unit'] ?? 'шт'); ?>
                                <option value="<?= (int) $ap['id'] ?>"><?= e(db_text($ap['name'])) ?> (<?= e(unit_label($u)) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="full" id="addNewNameWrap">
                        <label for="addNewName"><?= e(t('product_name')) ?></label>
                        <input type="text" name="add_new_name" id="addNewName" placeholder="<?= e(t('product_name_placeholder')) ?>">
                    </div>
                    <div class="full">
                        <label for="addDescription"><?= e(t('product_desc')) ?></label>
                        <textarea name="add_description" id="addDescription" rows="3" placeholder="<?= e(t('product_desc_placeholder')) ?>"></textarea>
                    </div>
                    <div>
                        <label for="addUnit"><?= e(t('unit_label')) ?></label>
                        <select name="add_unit" id="addUnit">
                            <option value="шт"><?= e(t('unit_pcs_full')) ?></option>
                            <option value="л"><?= e(t('unit_l_full')) ?></option>
                            <option value="кг"><?= e(t('unit_kg_full')) ?></option>
                            <option value="кв.м"><?= e(t('unit_sqm_full')) ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="addQty"><?= e(t('qty_received')) ?></label>
                        <input type="number" name="add_qty" id="addQty" min="0.001" step="1" value="1" required>
                    </div>
                    <div>
                        <label for="addCost"><?= e(t('cost_per_unit_short')) ?></label>
                        <input type="number" name="add_cost_unit" id="addCost" min="0" step="0.01" value="0">
                    </div>
                    <div>
                        <label for="addMarkup"><?= e(t('markup_pct')) ?></label>
                        <input type="number" name="add_markup_pct" id="addMarkup" min="0" step="0.01" value="100">
                    </div>
                    <div>
                        <label for="addDate"><?= e(t('receipt_date')) ?></label>
                        <input type="date" name="add_date" id="addDate" value="<?= e(date('Y-m-d')) ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-outline" id="addModalCancel"><?= e(t('cancel')) ?></button>
                <button type="submit" class="btn btn-primary"><?= e(t('add_to_stock')) ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal-backdrop" id="editModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <form method="POST" action="<?= url('warehouse') ?>" id="editForm">
            <div class="modal-head">
                <h3 id="modalTitle"><?= e(t('edit_product_title')) ?></h3>
                <button type="button" class="modal-close" id="modalClose" aria-label="<?= e(t('close')) ?>">×</button>
            </div>
            <div class="modal-body">
                <div class="modal-grid">
                    <div class="full"><label for="mProduct"><?= e(t('product_name_short')) ?></label><input type="text" id="mProduct" required></div>
                    <div class="full"><label for="mDescription"><?= e(t('product_desc')) ?></label><textarea id="mDescription" rows="3" placeholder="<?= e(t('product_desc_placeholder')) ?>"></textarea></div>
                    <div>
                        <label for="mUnit"><?= e(t('unit_label')) ?></label>
                        <select id="mUnit">
                            <option value="шт"><?= e(t('unit_pcs_full')) ?></option>
                            <option value="л"><?= e(t('unit_l_full')) ?></option>
                            <option value="кг"><?= e(t('unit_kg_full')) ?></option>
                            <option value="кв.м"><?= e(t('unit_sqm_full')) ?></option>
                        </select>
                    </div>
                    <div></div>
                    <div><label for="mQtyIn"><?= e(t('received')) ?>, <span id="mUnitLabelIn"></span></label><input type="number" id="mQtyIn" min="0" step="0.001"></div>
                    <div><label for="mQtyOut"><?= e(t('qty_sold')) ?>, <span id="mUnitLabelOut"></span></label><input type="number" id="mQtyOut" min="0" step="0.001"></div>
                    <div><label for="mCost"><?= e(t('cost_per_unit_short')) ?></label><input type="number" id="mCost" min="0" step="0.01"></div>
                    <div><label for="mMarkup"><?= e(t('markup_pct')) ?></label><input type="number" id="mMarkup" min="0" step="0.01"></div>
                </div>
                <div class="modal-calc" id="mCalc"></div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-outline" id="modalCancel"><?= e(t('cancel')) ?></button>
                <button type="submit" class="btn btn-primary" id="modalSave" name="save_id" value=""><?= e(t('save')) ?></button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<style>
    .stock-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:.85rem;}
    .stock-toolbar{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;flex:1 1 320px;justify-content:flex-end;}
    .stock-search{flex:1 1 260px;min-width:220px;max-width:420px;width:100%;padding:.6rem .85rem;background:var(--surface);border:1px solid var(--border);border-radius:10px;color:var(--text);font:inherit;display:block;}
    .stock-search:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--focus-ring);}
    .stock-search::placeholder{color:var(--muted);opacity:.85;}
    th a:hover{color:var(--accent);}
    .save-note { margin-bottom: 1rem; color: var(--income); font-weight: 600; }
    .modal-backdrop { display: none; position: fixed; inset: 0; background: var(--overlay); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(2px); }
    .modal-backdrop.open { display: flex; }
    .modal { width: 100%; max-width: 720px; background: var(--card); color: var(--text); border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-lg, var(--shadow)); overflow: hidden; }
    .modal-head { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); background: var(--card); }
    .modal-head h3 { margin: 0; font-size: 1.1rem; color: var(--text); }
    .modal-close { display:inline-flex; align-items:center; justify-content:center; padding:0; appearance:none; -webkit-appearance:none; background: var(--surface); border: 1px solid var(--border); color: var(--muted); width: 32px; height: 32px; border-radius: 8px; cursor: pointer; font-size: 1.1rem; line-height: 1; }
    .modal-close:hover { color: var(--text); border-color: var(--accent); }
    .modal-body { padding: 1.25rem; background: var(--card); }
    .modal-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .9rem 1rem; }
    .modal-grid .full { grid-column: 1 / -1; }
    .modal label { display: block; font-size: .85rem; color: var(--muted); margin-bottom: .3rem; }
    .modal input, .modal select, .modal textarea { width: 100%; padding: .55rem .65rem; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font: inherit; box-sizing: border-box; }
    .modal textarea { min-height: 84px; resize: vertical; }
    .modal-calc { font-size: .85rem; color: var(--muted); margin-top: .9rem; padding-top: .9rem; border-top: 1px solid var(--border); }
    .modal-foot { display: flex; gap: .75rem; justify-content: flex-end; padding: 1rem 1.25rem; border-top: 1px solid var(--border); background: var(--card); }
    .desc-cell { max-width: 260px; white-space: pre-wrap; color: var(--muted); font-size: .9rem; }
    @media (max-width: 640px) { .modal-grid { grid-template-columns: 1fr; } }
</style>
<script>
(function () {
    const I18N = {
        unitLabel: <?= json_encode(['шт'=>t('pcs'),'л'=>t('liter'),'кг'=>t('kg'),'кв.м'=>t('sqm')], JSON_UNESCAPED_UNICODE) ?>,
        stockQty: <?= json_encode(t('stock_qty'), JSON_UNESCAPED_UNICODE) ?>,
        salePriceUnit: <?= json_encode(t('sale_price_per_unit'), JSON_UNESCAPED_UNICODE) ?>,
        costStock: <?= json_encode(t('cost_stock'), JSON_UNESCAPED_UNICODE) ?>,
        saleStock: <?= json_encode(t('sale_stock'), JSON_UNESCAPED_UNICODE) ?>,
        currency: <?= json_encode(t('currency'), JSON_UNESCAPED_UNICODE) ?>
    };
    function ulabel(u){ return I18N.unitLabel[u] || u || I18N.unitLabel['шт']; }
    function fmt(n) { return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' '); }
    function fmtQty(n){ const x = Math.round(Number(n)*1000)/1000; if (Math.abs(x - Math.round(x)) < 1e-9) return String(Math.round(x)); return String(x).replace('.', ','); }
    function stepForUnit(u){ return (u === 'шт') ? '1' : '0.001'; }

    const modal = document.getElementById('editModal');
    if (modal) {
        const saveBtn = document.getElementById('modalSave');
        const fields = { product: document.getElementById('mProduct'), description: document.getElementById('mDescription'), unit: document.getElementById('mUnit'), qtyIn: document.getElementById('mQtyIn'), qtyOut: document.getElementById('mQtyOut'), cost: document.getElementById('mCost'), markup: document.getElementById('mMarkup') };
        const calc = document.getElementById('mCalc');
        const uIn = document.getElementById('mUnitLabelIn');
        const uOut = document.getElementById('mUnitLabelOut');
        function syncUnitUi(){ const u = fields.unit.value || 'шт'; const st = stepForUnit(u); const ul = ulabel(u); fields.qtyIn.step = st; fields.qtyOut.step = st; if(uIn) uIn.textContent = ul; if(uOut) uOut.textContent = ul; recalc(); }
        function recalc() {
            const qi = Math.max(0, parseFloat(fields.qtyIn.value || '0'));
            let qo = Math.max(0, parseFloat(fields.qtyOut.value || '0'));
            if (qo > qi) qo = qi;
            const stock = Math.round((qi - qo) * 1000) / 1000;
            const cost = Math.max(0, parseFloat(fields.cost.value || '0'));
            const markup = Math.max(0, parseFloat(fields.markup.value || '0'));
            const sale = cost * (100 + markup) / 100;
            const ul = ulabel(fields.unit.value || 'шт');
            calc.textContent = I18N.stockQty + ': ' + fmtQty(stock) + ' ' + ul + ' · ' + I18N.salePriceUnit + ': ' + fmt(sale) + ' ' + I18N.currency + ' · ' + I18N.costStock + ': ' + fmt(stock * cost) + ' ' + I18N.currency + ' · ' + I18N.saleStock + ': ' + fmt(stock * sale) + ' ' + I18N.currency;
        }
        ['qtyIn','qtyOut','cost','markup'].forEach(k => fields[k].addEventListener('input', recalc));
        fields.unit.addEventListener('change', syncUnitUi);
        function setNames(id) {
            fields.product.name = 'product[' + id + ']';
            fields.description.name = 'description[' + id + ']';
            fields.unit.name = 'unit[' + id + ']';
            fields.qtyIn.name = 'qty_in[' + id + ']';
            fields.qtyOut.name = 'qty_out[' + id + ']';
            fields.cost.name = 'cost_unit[' + id + ']';
            fields.markup.name = 'markup_pct[' + id + ']';
            saveBtn.value = id;
        }
        function openModal(btn) {
            const id = btn.dataset.id;
            setNames(id);
            fields.product.value = btn.dataset.product || '';
            fields.description.value = btn.dataset.description || '';
            fields.unit.value = btn.dataset.unit || 'шт';
            fields.qtyIn.value = btn.dataset.qtyIn || '0';
            fields.qtyOut.value = btn.dataset.qtyOut || '0';
            fields.cost.value = btn.dataset.costUnit || '0';
            fields.markup.value = btn.dataset.markupPct || '0';
            syncUnitUi();
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            fields.product.focus();
        }
        function closeModal() { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }
        document.querySelectorAll('.btn-edit').forEach(btn => btn.addEventListener('click', () => openModal(btn)));
        document.getElementById('modalClose').addEventListener('click', closeModal);
        document.getElementById('modalCancel').addEventListener('click', closeModal);
        modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) closeModal(); });
    }

    const addModal = document.getElementById('addModal');
    const addOpenBtn = document.getElementById('btnAddStock');
    function openAddModal() { if (addModal) { addModal.classList.add('open'); addModal.setAttribute('aria-hidden', 'false'); } }
    function closeAddModal() { if (addModal) { addModal.classList.remove('open'); addModal.setAttribute('aria-hidden', 'true'); } }
    if (addOpenBtn) addOpenBtn.addEventListener('click', openAddModal);
    const addCloseBtn = document.getElementById('addModalClose');
    const addCancelBtn = document.getElementById('addModalCancel');
    if (addCloseBtn) addCloseBtn.addEventListener('click', closeAddModal);
    if (addCancelBtn) addCancelBtn.addEventListener('click', closeAddModal);
    if (addModal) addModal.addEventListener('click', e => { if (e.target === addModal) closeAddModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && addModal && addModal.classList.contains('open')) closeAddModal(); });

    const addProduct = document.getElementById('addProduct');
    const addNewName = document.getElementById('addNewName');
    const addNewNameWrap = document.getElementById('addNewNameWrap');
    const addDesc = document.getElementById('addDescription');
    const addCost = document.getElementById('addCost');
    const addMarkup = document.getElementById('addMarkup');
    const addUnit = document.getElementById('addUnit');
    const addQty = document.getElementById('addQty');
    const addProducts = <?= json_encode(array_map(static function ($p) {
        return ['id' => (int)$p['id'], 'name' => (string)db_text($p['name']), 'description' => (string)db_text($p['description'] ?? ''), 'unit' => normalize_unit($p['unit'] ?? 'шт'), 'cost_unit' => (float)$p['cost_unit'], 'markup_pct' => (float)$p['markup_pct']];
    }, $allProducts), JSON_UNESCAPED_UNICODE) ?>;
    function syncAddMode() {
        if (!addProduct) return;
        const hasExisting = !!addProduct.value;
        if (addNewNameWrap) addNewNameWrap.style.display = hasExisting ? 'none' : '';
        if (addNewName) { addNewName.disabled = hasExisting; if (hasExisting) addNewName.value = ''; }
        const u = (addUnit && addUnit.value) || 'шт';
        if (addQty) addQty.step = stepForUnit(u);
        if (hasExisting) {
            const p = addProducts.find(x => String(x.id) === String(addProduct.value));
            if (p) {
                if (addUnit) { addUnit.value = p.unit || 'шт'; addUnit.disabled = true; }
                if (addQty) addQty.step = stepForUnit(p.unit || 'шт');
                if (addCost) addCost.value = p.cost_unit || 0;
                if (addMarkup) addMarkup.value = p.markup_pct || 100;
                if (addDesc && !addDesc.dataset.touched) addDesc.value = p.description || '';
            }
        } else {
            if (addUnit) addUnit.disabled = false;
        }
    }
    if (addProduct) {
        addProduct.addEventListener('change', function(){ if(addDesc) addDesc.dataset.touched=''; syncAddMode(); });
        if (addUnit) addUnit.addEventListener('change', function(){ if(addQty) addQty.step = stepForUnit(this.value || 'шт'); });
        if (addDesc) addDesc.addEventListener('input', function(){ this.dataset.touched = '1'; });
        syncAddMode();
    }

    const stockSearch = document.getElementById('stockSearch');
    if (stockSearch) {
        stockSearch.addEventListener('input', function(){
            const q = (this.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('tr.stock-row');
            let visible = 0;
            rows.forEach(tr => {
                const text = (tr.textContent || '').toLowerCase();
                const show = !q || text.indexOf(q) !== -1;
                tr.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            let noRow = document.getElementById('stockNoResultsRow');
            if (!noRow) {
                const tbody = rows.length ? rows[0].parentElement : null;
                if (tbody) {
                    noRow = document.createElement('tr');
                    noRow.id = 'stockNoResultsRow';
                    const td = document.createElement('td');
                    const totalRow = document.querySelector('tr.stock-total-row');
                    const colCount = totalRow ? totalRow.children.length : (rows[0] ? rows[0].children.length : 12);
                    td.colSpan = colCount;
                    td.style.textAlign = 'center'; td.style.color = 'var(--muted)'; td.style.padding = '2rem';
                    td.textContent = <?= json_encode(t('no_search_results'), JSON_UNESCAPED_UNICODE) ?>;
                    noRow.appendChild(td); noRow.style.display = 'none';
                    tbody.appendChild(noRow);
                }
            }
            if (noRow) noRow.style.display = (q && visible === 0) ? '' : 'none';
        });
    }
})();
</script>