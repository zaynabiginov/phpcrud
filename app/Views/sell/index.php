<?php if ($message): ?>
<div id="toast" style="position:fixed; top:1rem; right:1rem; background:var(--card); border:1px solid var(--border); color:var(--text); padding:1rem 1.5rem; border-radius:10px; box-shadow:var(--shadow); z-index:50; border-left:4px solid <?= $messageType === 'error' ? 'var(--expense)' : 'var(--income)' ?>">
    <?= e($message) ?>
</div>
<?php endif; ?>

<section class="card" style="max-width:640px; margin:0 auto;">
    <h2 style="margin-bottom:.25rem;"><?= e(t('create_sale_title')) ?></h2>
    <p style="color:var(--muted); margin:0 0 1.25rem; font-size:.9rem;"><?= e(t('sell_hint')) ?></p>
    <form action="<?= url('sales') ?>" method="POST" class="form-grid" novalidate id="sellForm">
        <div class="form-group" style="grid-column: 1 / -1;">
            <label for="productTrigger"><?= e(t('product_label')) ?></label>
            <div class="search-select" id="productSelect">
                <input type="hidden" name="product_id" id="product" required value="<?= e((string)($old['product_id'] ?? '')) ?>">
                <button type="button" class="search-select-trigger" id="productTrigger" aria-haspopup="listbox" aria-expanded="false"><?= e(t('loading')) ?></button>
                <div class="search-select-panel" id="productPanel" hidden>
                    <div class="search-select-search">
                        <input type="text" id="productSearch" placeholder="<?= e(t('search_product')) ?>" autocomplete="off">
                    </div>
                    <div class="search-select-options" id="productOptions" role="listbox"></div>
                </div>
            </div>
            <small style="color:var(--muted); display:block; margin-top:.35rem;"><?= e(t('open_list_search')) ?></small>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
            <label><?= e(t('product_desc_label')) ?></label>
            <textarea id="productDesc" rows="3" readonly style="background:var(--surface-2); color:var(--muted); resize:vertical;" placeholder="<?= e(t('desc_placeholder')) ?>"></textarea>
        </div>
        <div class="form-group">
            <label><?= e(t('in_stock')) ?></label>
            <input type="text" id="stockInfo" value="—" readonly style="background:var(--surface-2); color:var(--muted);">
        </div>
        <div class="form-group">
            <label id="qtyLabel"><?= e(t('quantity')) ?></label>
            <input type="number" name="quantity" id="quantity" step="0.001" min="0.001" placeholder="1" value="<?= e($old['quantity'] ?? '1') ?>" required>
        </div>
        <div class="form-group">
            <label id="priceLabel"><?= e(t('sale_price_unit_label')) ?></label>
            <input type="text" id="unitPrice" value="—" readonly style="background:var(--surface-2); color:var(--muted);">
        </div>
        <div class="form-group">
            <label for="totalAmount"><?= e(t('sale_amount')) ?></label>
            <input type="number" name="amount" id="totalAmount" step="0.01" min="0" value="<?= e($old['amount'] ?? '') ?>" placeholder="—" required style="background:var(--surface); color:var(--text); font-weight:700;">
            <small id="amountHint" style="color:var(--muted); display:block; margin-top:.35rem;"><?= e(t('amount_hint')) ?></small>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
            <label><?= e(t('date')) ?></label>
            <input type="date" name="date" value="<?= e($old['date'] ?? date('Y-m-d')) ?>" required>
        </div>
        <div class="form-group" style="grid-column: 1 / -1; display:flex; gap:0.75rem;">
            <button type="submit" class="btn btn-primary" id="submitBtn" style="flex:1;"><?= e(t('submit_sale')) ?></button>
            <a href="<?= url('warehouse') ?>" class="btn btn-outline"><?= e(t('cancel')) ?></a>
        </div>
    </form>
</section>

<style>
    .search-select { position: relative; }
    .search-select-trigger { width:100%; text-align:left; padding:.55rem .65rem; background:var(--surface); border:1px solid var(--border); border-radius:8px; color:var(--text); font:inherit; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:.5rem; box-sizing:border-box; }
    .search-select-trigger::after { content:'▾'; color:var(--muted); font-size:.8rem; }
    .search-select-trigger:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--focus-ring); }
    .search-select.open .search-select-trigger { border-color:var(--accent); box-shadow:0 0 0 3px var(--focus-ring); }
    .search-select-panel { position:absolute; left:0; right:0; top:calc(100% + 4px); background:var(--card); border:1px solid var(--border); border-radius:10px; box-shadow:var(--shadow-lg, var(--shadow)); z-index:50; overflow:hidden; }
    .search-select-panel[hidden] { display:none; }
    .search-select-search { padding:.5rem; border-bottom:1px solid var(--border); background:var(--card); }
    .search-select-search input { width:100%; padding:.5rem .6rem; background:var(--surface); border:1px solid var(--border); border-radius:8px; color:var(--text); font:inherit; box-sizing:border-box; }
    .search-select-options { max-height:260px; overflow:auto; }
    .search-select-option { padding:.55rem .65rem; cursor:pointer; border-bottom:1px solid var(--border); }
    .search-select-option:hover, .search-select-option.active { background:var(--surface); }
    .search-select-option.selected { color:var(--accent); font-weight:600; }
    .search-select-option.disabled { color:var(--muted); cursor:not-allowed; opacity:.7; }
    .search-select-empty { padding:.75rem .65rem; color:var(--muted); }
</style>
<script>
(function () {
    const I18N = {
        loading: <?= json_encode(t('loading'), JSON_UNESCAPED_UNICODE) ?>,
        selectProduct: <?= json_encode(t('select_product'), JSON_UNESCAPED_UNICODE) ?>,
        inStockLabel: <?= json_encode(t('in_stock'), JSON_UNESCAPED_UNICODE) ?>,
        quantity: <?= json_encode(t('quantity'), JSON_UNESCAPED_UNICODE) ?>,
        salePriceUnit: <?= json_encode(t('sale_price_unit_label'), JSON_UNESCAPED_UNICODE) ?>,
        noProducts: <?= json_encode(t('no_products'), JSON_UNESCAPED_UNICODE) ?>,
        nothingFound: <?= json_encode(t('nothing_found'), JSON_UNESCAPED_UNICODE) ?>,
        notAvailable: <?= json_encode(t('not_available'), JSON_UNESCAPED_UNICODE) ?>,
        outOfStock: <?= json_encode(t('out_of_stock'), JSON_UNESCAPED_UNICODE) ?>,
        exceedsStock: <?= json_encode(t('exceeds_stock'), JSON_UNESCAPED_UNICODE) ?>,
        pcsInteger: <?= json_encode(t('pcs_integer'), JSON_UNESCAPED_UNICODE) ?>,
        enterQuantity: <?= json_encode(t('enter_quantity'), JSON_UNESCAPED_UNICODE) ?>,
        range: <?= json_encode(t('range'), JSON_UNESCAPED_UNICODE) ?>,
        calc: <?= json_encode(t('calc'), JSON_UNESCAPED_UNICODE) ?>,
        amountHint: <?= json_encode(t('amount_hint'), JSON_UNESCAPED_UNICODE) ?>,
        currency: <?= json_encode(t('currency'), JSON_UNESCAPED_UNICODE) ?>,
        loadError: <?= json_encode(t('load_error'), JSON_UNESCAPED_UNICODE) ?>,
        unitLabel: <?= json_encode(['шт'=>t('pcs'),'л'=>t('liter'),'кг'=>t('kg'),'кв.м'=>t('sqm')], JSON_UNESCAPED_UNICODE) ?>
    };
    function ulabel(u){ return I18N.unitLabel[u] || u || I18N.unitLabel['шт']; }
    const product = document.getElementById('product');
    const selectWrap = document.getElementById('productSelect');
    const trigger = document.getElementById('productTrigger');
    const panel = document.getElementById('productPanel');
    const search = document.getElementById('productSearch');
    const optionsEl = document.getElementById('productOptions');
    const qty = document.getElementById('quantity');
    const stockInfo = document.getElementById('stockInfo');
    const unitPrice = document.getElementById('unitPrice');
    const totalAmount = document.getElementById('totalAmount');
    const amountHint = document.getElementById('amountHint');
    const submitBtn = document.getElementById('submitBtn');
    const productDesc = document.getElementById('productDesc');
    const qtyLabel = document.getElementById('qtyLabel');
    const priceLabel = document.getElementById('priceLabel');
    let products = [];
    let amountTouched = !!(totalAmount && totalAmount.value);
    function fmt(n) { return Math.round(Number(n)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' '); }
    function fmtQty(n){ const x=Math.round(Number(n)*1000)/1000; if (Math.abs(x-Math.round(x))<1e-9) return String(Math.round(x)); return String(x).replace('.',','); }
    function stepForUnit(u){ return (u==='шт') ? '1' : '0.001'; }
    function moneyPlain(n){ return (Math.round(Number(n)*100)/100).toFixed(2); }
    function currentProduct(){ const id=parseInt(product.value,10); return products.find(x=>parseInt(x.id,10)===id) || null; }
    function setSelected(p){
        amountTouched = false;
        if (!p) { product.value = ''; if (trigger) trigger.textContent = I18N.selectProduct; }
        else { product.value = String(p.id); if (trigger) trigger.textContent = p.name + ' (' + I18N.inStockLabel.toLowerCase() + ': ' + fmtQty(p.stock_qty||0) + ' ' + ulabel(p.unit||'шт') + ')'; }
        updateCalc(); renderOptions();
    }
    function openPanel(){ if (!panel || !selectWrap || trigger.disabled) return; panel.hidden = false; selectWrap.classList.add('open'); trigger.setAttribute('aria-expanded','true'); renderOptions(); if (search) { search.focus(); search.select(); } }
    function closePanel(){ if (!panel || !selectWrap) return; panel.hidden = true; selectWrap.classList.remove('open'); trigger.setAttribute('aria-expanded','false'); }
    function renderOptions(){
        if (!optionsEl) return;
        const q = ((search && search.value) || '').trim().toLowerCase();
        const cur = String(product.value || '');
        const filtered = q ? products.filter(p => ((p.name || '') + ' ' + (p.description || '')).toLowerCase().includes(q)) : products.slice();
        optionsEl.innerHTML = '';
        if (!filtered.length) { const empty = document.createElement('div'); empty.className = 'search-select-empty'; empty.textContent = q ? I18N.nothingFound : I18N.noProducts; optionsEl.appendChild(empty); return; }
        filtered.forEach(p => {
            const u = p.unit || 'шт'; const stock = parseFloat(p.stock_qty) || 0;
            const opt = document.createElement('div');
            opt.className = 'search-select-option' + (String(p.id)===cur ? ' selected' : '') + (stock <= 0.0005 ? ' disabled' : '');
            opt.setAttribute('role','option'); opt.setAttribute('aria-selected', String(p.id)===cur ? 'true' : 'false');
            opt.textContent = p.name + ' (' + I18N.inStockLabel.toLowerCase() + ': ' + fmtQty(stock) + ' ' + ulabel(u) + ')';
            if (stock <= 0.0005) opt.title = I18N.notAvailable;
            else opt.addEventListener('click', () => { setSelected(p); closePanel(); trigger.focus(); });
            optionsEl.appendChild(opt);
        });
    }
    function resetAmount(msg){ totalAmount.min = '0'; totalAmount.max = ''; if (!amountTouched) totalAmount.value = ''; totalAmount.placeholder = '—'; if (amountHint) amountHint.textContent = msg || I18N.amountHint; }
    function updateCalc() {
        const p = currentProduct(); const u = (p && p.unit) ? p.unit : 'шт'; const ul = ulabel(u);
        qty.step = stepForUnit(u);
        if (qtyLabel) qtyLabel.textContent = I18N.quantity + ' (' + ul + ')';
        if (priceLabel) priceLabel.textContent = I18N.salePriceUnit;
        if (!p) { stockInfo.value = '—'; unitPrice.value = '—'; productDesc.value = ''; qty.max = ''; submitBtn.disabled = true; resetAmount(); return; }
        const stock = Math.max(0, parseFloat(p.stock_qty) || 0); const price = parseFloat(p.sale_price) || 0; const q = Math.max(0, parseFloat((qty.value || '0').replace(',', '.')) || 0);
        stockInfo.value = fmtQty(stock) + ' ' + ul; unitPrice.value = fmt(price) + ' ' + I18N.currency; productDesc.value = p.description || ''; qty.max = stock > 0 ? String(stock) : '';
        if (stock <= 0.0005) { submitBtn.disabled = true; resetAmount(I18N.outOfStock); return; }
        if (q - stock > 0.0005) { submitBtn.disabled = true; resetAmount(I18N.exceedsStock); return; }
        if (q <= 0 || (u === 'шт' && Math.abs(q - Math.round(q)) > 1e-9)) { submitBtn.disabled = true; resetAmount(u === 'шт' ? I18N.pcsInteger : I18N.enterQuantity); return; }
        const calc = price * q; const min = calc * 0.15;
        totalAmount.min = moneyPlain(min); totalAmount.max = moneyPlain(calc);
        if (amountHint) amountHint.textContent = I18N.range + fmt(min) + ' — ' + fmt(calc) + ' ' + I18N.currency + ' (' + I18N.calc + fmt(calc) + ' ' + I18N.currency + ').';
        const cur = parseFloat(String(totalAmount.value).replace(',', '.'));
        if (!amountTouched || !totalAmount.value || !(isFinite(cur) && cur + 0.001 >= min && cur - 0.001 <= calc)) { totalAmount.value = moneyPlain(calc); amountTouched = false; }
        submitBtn.disabled = false;
    }
    if (trigger) { trigger.disabled = true; trigger.addEventListener('click', () => { if (panel.hidden) openPanel(); else closePanel(); }); }
    if (search) search.addEventListener('input', renderOptions);
    totalAmount.addEventListener('input', () => { amountTouched = true; });
    document.addEventListener('click', (e) => { if (selectWrap && !selectWrap.contains(e.target)) closePanel(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && panel && !panel.hidden) { closePanel(); if (trigger) trigger.focus(); } });
    fetch('api/products').then(r => r.json()).then(data => {
        products = Array.isArray(data) ? data : [];
        if (trigger) { trigger.disabled = false; trigger.textContent = I18N.selectProduct; }
        renderOptions();
        const pre = parseInt(product.value, 10);
        if (pre) { const p = products.find(x => parseInt(x.id,10)===pre); if (p) setSelected(p); }
        updateCalc();
    }).catch(() => { if (trigger) { trigger.disabled = false; trigger.textContent = I18N.loadError; } updateCalc(); });
    qty.addEventListener('input', () => { updateCalc(); });
    updateCalc();
})();
</script>