<?php if (!empty($message)): ?>
<div class="card" style="margin-bottom:1rem; border-left:4px solid var(--expense);"><?= e($message) ?></div>
<?php endif; ?>

<section class="dash-header">
    <div class="dash-head-left">
        <h1 class="dash-title"><?= e(t('dashboard')) ?></h1>
        <p class="dash-sub"><?= e(t('dashboard_subtitle')) ?></p>
    </div>
</section>

<div class="kpi-grid dash-kpi">
    <div class="card kpi kpi-income">
        <span class="kpi-label"><?= e(t('today_revenue')) ?></span>
        <span class="kpi-value"><?= fmt_money0($todayStats['revenue'] ?? 0) ?></span>
        <span class="kpi-sub"><?= e(t('currency')) ?></span>
    </div>
    <div class="card kpi kpi-expense">
        <span class="kpi-label"><?= e(t('today_sales')) ?></span>
        <span class="kpi-value"><?= (int)($todayStats['sales'] ?? 0) ?></span>
        <span class="kpi-sub"><?= e(t('units')) ?></span>
    </div>
    <div class="card kpi kpi-count">
        <span class="kpi-label"><?= e(t('total_products')) ?></span>
        <span class="kpi-value"><?= (int)($stats['total_products'] ?? 0) ?></span>
        <span class="kpi-sub"><?= e(t('in_catalog')) ?></span>
    </div>
    <div class="card kpi kpi-balance">
        <span class="kpi-label"><?= e(t('in_stock_positions')) ?></span>
        <span class="kpi-value"><?= (int)($stats['in_stock'] ?? 0) ?></span>
        <span class="kpi-sub"><?= e(t('stock_gt_zero')) ?></span>
    </div>
    <div class="card kpi <?= ((int)($stats['low_stock'] ?? 0) > 0) ? 'kpi-expense' : 'kpi-balance' ?>">
        <span class="kpi-label"><?= e(t('low_stock')) ?></span>
        <span class="kpi-value"><?= (int)($stats['low_stock'] ?? 0) ?></span>
        <span class="kpi-sub"><?= e(t('low_stock_hint')) ?></span>
    </div>
    <div class="card kpi <?= ((int)($stats['out_stock'] ?? 0) > 0) ? 'kpi-expense' : 'kpi-balance' ?>">
        <span class="kpi-label"><?= e(t('out_of_stock_kpi')) ?></span>
        <span class="kpi-value"><?= (int)($stats['out_stock'] ?? 0) ?></span>
        <span class="kpi-sub"><?= e(t('product')) ?></span>
    </div>
</div>

<section class="card dash-actions">
    <h2 style="margin:0 0 .75rem; font-size:1.05rem;"><?= e(t('quick_actions')) ?></h2>
    <div class="dash-actions-grid">
        <a href="<?= url('sales/new') ?>" class="dash-action dash-action-primary">
            <span class="dash-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg></span>
            <span class="dash-action-text"><strong><?= e(t('go_to_sales')) ?></strong><small><?= e(t('create_sale_title')) ?></small></span>
        </a>
        <a href="<?= url('warehouse') ?>" class="dash-action">
            <span class="dash-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v10l9 4 9-4V7"/><path d="M12 11v10"/></svg></span>
            <span class="dash-action-text"><strong><?= e(t('go_to_warehouse')) ?></strong><small><?= e(t('stock_items')) ?></small></span>
        </a>
        <?php if (is_admin()): ?>
        <a href="<?= url('report') ?>" class="dash-action">
            <span class="dash-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19h16"/><rect x="6" y="10" width="3" height="6" rx="1"/><rect x="11" y="7" width="3" height="9" rx="1"/><rect x="16" y="4" width="3" height="12" rx="1"/></svg></span>
            <span class="dash-action-text"><strong><?= e(t('go_to_report')) ?></strong><small><?= e(t('report')) ?></small></span>
        </a>
        <a href="<?= url('users') ?>" class="dash-action">
            <span class="dash-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-7A3.5 3.5 0 0 0 2 17.5V19"/><circle cx="9" cy="8" r="3.5"/></svg></span>
            <span class="dash-action-text"><strong><?= e(t('go_to_users')) ?></strong><small><?= e(t('users')) ?></small></span>
        </a>
        <?php endif; ?>
    </div>
</section>

<?php
    $filterType = $filterType ?? 'all';
    $searchQuery = trim((string)($searchQuery ?? ''));
    $perPageParam = (string)($perPageParam ?? '30');
    if (!in_array($perPageParam, ['30', '50', '70', 'all'], true)) $perPageParam = '30';
    $page = max(1, (int)($page ?? 1));
    $totalOps = (int)($totalOps ?? 0);
    $totalPages = max(1, (int)($totalPages ?? 1));
    $perPage = max(1, (int)($perPage ?? 30));
    $from = $totalOps ? (($page - 1) * $perPage + 1) : 0;
    $to = $totalOps ? min($page * $perPage, $totalOps) : 0;
    $baseQuery = function(array $overrides = []) use ($filterType, $perPageParam, $searchQuery) {
        $q = array_merge(['type' => $filterType, 'per_page' => $perPageParam, 'q' => $searchQuery], $overrides);
        if (($q['type'] ?? 'all') === 'all') unset($q['type']);
        if ((string)($q['per_page'] ?? '30') === '30') unset($q['per_page']);
        if (($q['q'] ?? '') === '') unset($q['q']);
        if (($q['page'] ?? 1) <= 1) unset($q['page']);
        $qs = http_build_query(array_filter($q, fn($v) => $v !== '' && $v !== null));
        return url('home') . ($qs ? ('?' . $qs) : '');
    };
    $hasFilters = ($filterType !== 'all' || $perPageParam !== '30' || $searchQuery !== '');
?>
<section class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:.75rem;">
        <h2 style="margin:0; font-size:1.1rem;"><?= e(t('all_operations')) ?></h2>
        <form method="GET" action="<?= url('home') ?>" class="ops-filter">
            <label for="opsSearch" class="sr-only"><?= e(t('search_product')) ?></label>
            <input id="opsSearch" type="text" name="q" value="<?= e($searchQuery) ?>" class="filter-select" style="min-width:220px;" placeholder="<?= e(t('search_product')) ?>">
            <label for="opsType" class="sr-only"><?= e(t('type')) ?></label>
            <select id="opsType" name="type" class="filter-select">
                <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>><?= e(t('all')) ?></option>
                <option value="income" <?= $filterType === 'income' ? 'selected' : '' ?>><?= e(t('income')) ?></option>
                <option value="expense" <?= $filterType === 'expense' ? 'selected' : '' ?>><?= e(t('sold')) ?></option>
            </select>
            <label for="opsPerPage" class="sr-only"><?= e(t('per_page')) ?></label>
            <select id="opsPerPage" name="per_page" class="filter-select" style="min-width:110px;">
                <option value="30" <?= $perPageParam === '30' ? 'selected' : '' ?>>30</option>
                <option value="50" <?= $perPageParam === '50' ? 'selected' : '' ?>>50</option>
                <option value="70" <?= $perPageParam === '70' ? 'selected' : '' ?>>70</option>
                <option value="all" <?= $perPageParam === 'all' ? 'selected' : '' ?>><?= e(t('all')) ?></option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary"><?= e(t('show')) ?></button>
            <?php if ($hasFilters): ?>
                <a href="<?= url('home') ?>" class="btn btn-sm btn-outline"><?= e(t('reset')) ?></a>
            <?php endif; ?>
        </form>
    </div>
    <div style="color:var(--muted); font-size:.9rem; margin-bottom:.5rem;">
        <?= e(t('shown')) ?>: <?= $from ?>–<?= $to ?> / <?= $totalOps ?>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th><?= e(t('date')) ?></th><th><?= e(t('product')) ?></th><th><?= e(t('type')) ?></th><th><?= e(t('qty')) ?></th><th><?= e(t('amount')) ?></th><th><?= e(t('sold_by')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent)): foreach ($recent as $row): $u = normalize_unit($row['unit'] ?? 'шт'); $seller = ($row['type'] === 'expense') ? trim((string)($row['performed_by'] ?? '')) : ''; ?>
                <tr>
                    <td><?= e($row['transaction_date']) ?></td>
                    <td><?= e(db_text($row['product'])) ?></td>
                    <td class="type-cell"><span class="badge badge-<?= e($row['type']) ?>"><?= $row['type'] === 'income' ? e(t('income')) : e(t('sold')) ?></span></td>
                    <td><?= fmt_qty($row['quantity'] ?? 0) . ' ' . e(unit_label($u)) ?></td>
                    <td><?= fmt_money0($row['amount']) ?> <?= e(t('currency')) ?></td>
                    <td><?= $seller !== '' ? e($seller) : '—' ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align:center; color: var(--muted); padding: 2rem;"><?= e(($searchQuery ?? '') !== '' ? t('nothing_found') : t('no_ops')) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="pagination">
        <?php if ($page > 1): ?>
            <a class="page-link" href="<?= e($baseQuery(['page' => $page - 1])) ?>">&laquo; <?= e(t('prev')) ?></a>
        <?php else: ?>
            <span class="page-link disabled">&laquo; <?= e(t('prev')) ?></span>
        <?php endif; ?>
        <span class="page-info"><?= e(t('page_n')) ?> <?= $page ?> / <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="page-link" href="<?= e($baseQuery(['page' => $page + 1])) ?>"><?= e(t('next')) ?> &raquo;</a>
        <?php else: ?>
            <span class="page-link disabled"><?= e(t('next')) ?> &raquo;</span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</section>

<style>
.dash-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;}
.dash-head-left{min-width:180px;}
.dash-title{margin:0;font-size:1.6rem;font-weight:800;letter-spacing:-.3px;color:var(--text);}
.dash-sub{margin:.25rem 0 0;color:var(--muted);font-size:.95rem;}
.dash-kpi{grid-template-columns:repeat(auto-fit,minmax(170px,1fr));margin-bottom:1.25rem;}
.dash-actions{margin-bottom:1.25rem;}
.dash-actions-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem;}
.dash-action{display:flex;align-items:center;gap:.85rem;padding:.9rem 1rem;border:1px solid var(--border);border-radius:12px;background:var(--surface);color:var(--text);text-decoration:none;transition:border-color .18s,background .18s,transform .12s;}
.dash-action:hover{border-color:var(--accent);background:var(--hover-row);}
.dash-action-primary{border-color:rgba(56,189,248,.35);background:rgba(56,189,248,.08);}
.dash-action-icon{width:40px;height:40px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--accent),var(--accent-2));color:#fff;flex:0 0 auto;}
.dash-action-icon svg{width:20px;height:20px;}
.dash-action-text{display:flex;flex-direction:column;line-height:1.25;}
.dash-action-text strong{font-size:.98rem;}
.dash-action-text small{color:var(--muted);font-size:.82rem;}

.ops-filter{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.filter-select{padding:.45rem .6rem;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font:inherit;min-width:180px;}
.pagination{display:flex;align-items:center;justify-content:center;gap:.5rem;flex-wrap:wrap;margin-top:1rem;}
.page-link{padding:.4rem .7rem;border:1px solid var(--border);border-radius:8px;color:var(--text);text-decoration:none;background:var(--surface);}
.page-link:hover{border-color:var(--accent);background:var(--hover-row);}
.page-link.disabled{opacity:.45;pointer-events:none;}
.page-info{color:var(--muted);font-size:.92rem;padding:0 .25rem;}
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
</style>