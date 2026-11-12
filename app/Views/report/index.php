<section class="card" style="margin-bottom:1.5rem;">
    <form method="GET" action="<?= url('report') ?>" class="report-filters">
        <div class="filter-row">
            <div class="form-group">
                <label><?= e(t('period_from')) ?></label>
                <input type="date" name="from" value="<?= e($from) ?>" required>
            </div>
            <div class="form-group">
                <label><?= e(t('period_to')) ?></label>
                <input type="date" name="to" value="<?= e($to) ?>" required>
            </div>
            <div class="form-group">
                <label><?= e(t('grouping')) ?></label>
                <select name="group">
                    <option value="month" <?= $group === 'month' ? 'selected' : '' ?>><?= e(t('by_months')) ?></option>
                    <option value="day" <?= $group === 'day' ? 'selected' : '' ?>><?= e(t('by_days')) ?></option>
                </select>
            </div>
            <div class="form-group">
                <label><?= e(t('flow_filter')) ?></label>
                <select name="flow">
                    <option value="all" <?= $flow === 'all' ? 'selected' : '' ?>><?= e(t('all_operations')) ?></option>
                    <option value="income" <?= $flow === 'income' ? 'selected' : '' ?>><?= e(t('income_only')) ?></option>
                    <option value="expense" <?= $flow === 'expense' ? 'selected' : '' ?>><?= e(t('expense_only')) ?></option>
                </select>
            </div>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><?= e(t('show')) ?></button>
            <a href="<?= url('report') ?>" class="btn btn-outline"><?= e(t('reset')) ?></a>
        </div>
        <input type="hidden" name="sort" value="<?= e($sort) ?>">
        <input type="hidden" name="dir" value="<?= e(strtolower($dir)) ?>">
    </form>
</section>

<section class="card report-section" style="margin-bottom:1.5rem;">
    <div class="report-section-head">
        <div>
            <h2 style="margin:0;"><?= e(t('overall_summary')) ?></h2>
            <p class="report-note" style="margin:.25rem 0 0;"><?= e(t('overall_summary_hint')) ?></p>
        </div>
        <span class="report-badge"><?= e($from) ?> — <?= e($to) ?></span>
    </div>

    <div class="report-block">
        <h3 class="report-block-title"><?= e(t('cost_block')) ?></h3>
        <div class="kpi-grid kpi-grid-compact">
            <div class="card kpi kpi-balance">
                <span class="kpi-label"><?= e(t('cogs_kpi')) ?></span>
                <span class="kpi-value"><?= fmt_money($overallCost) ?></span>
                <span class="kpi-sub"><?= e(t('currency')) ?> <?= e(t('by_cost_unit')) ?></span>
            </div>
            <div class="card kpi kpi-expense">
                <span class="kpi-label"><?= e(t('revenue_kpi')) ?></span>
                <span class="kpi-value"><?= fmt_money($overallExpense) ?></span>
                <span class="kpi-sub"><?= fmt_qty($overallSalesQty) ?> <?= e(t('units')) ?></span>
            </div>
            <div class="card kpi kpi-income">
                <span class="kpi-label"><?= e(t('income_kpi')) ?></span>
                <span class="kpi-value"><?= fmt_money($overallIncome) ?></span>
                <span class="kpi-sub"><?= fmt_qty($overallInQty) ?> <?= e(t('units')) ?></span>
            </div>
        </div>
    </div>

    <div class="report-block">
        <h3 class="report-block-title"><?= e(t('margin_block')) ?></h3>
        <div class="kpi-grid kpi-grid-compact">
            <div class="card kpi <?= $overallMargin >= 0 ? 'kpi-income' : 'kpi-expense' ?>">
                <span class="kpi-label"><?= e(t('margin_pct_label')) ?></span>
                <span class="kpi-value"><?= number_format((float)$overallMargin, 1, ',', ' ') ?>%</span>
                <span class="kpi-sub"><?= e(t('margin_formula')) ?></span>
            </div>
            <div class="card kpi <?= $overallMarkup >= 0 ? 'kpi-income' : 'kpi-expense' ?>">
                <span class="kpi-label"><?= e(t('markup_pct_label')) ?></span>
                <span class="kpi-value"><?= number_format((float)$overallMarkup, 1, ',', ' ') ?>%</span>
                <span class="kpi-sub"><?= e(t('markup_formula')) ?></span>
            </div>
        </div>
    </div>

    <div class="report-block">
        <h3 class="report-block-title"><?= e(t('profit_block')) ?></h3>
        <div class="kpi-grid kpi-grid-compact">
            <div class="card kpi <?= $overallProfit >= 0 ? 'kpi-income' : 'kpi-expense' ?>">
                <span class="kpi-label"><?= e(t('gross_profit_kpi')) ?></span>
                <span class="kpi-value"><?= fmt_money($overallProfit) ?></span>
                <span class="kpi-sub"><?= e(t('currency')) ?> · <?= e(t('profit_formula')) ?></span>
            </div>
        </div>
    </div>
</section>

<?php $flowLabel = $flow === 'income' ? t('income_only') : ($flow === 'expense' ? t('expense_only') : t('all_operations')); ?>
<section class="card report-section" style="margin-bottom:1.5rem;">
    <div class="report-section-head">
        <div>
            <h2 style="margin:0;"><?= e(t('filtered_view')) ?></h2>
            <p class="report-note" style="margin:.25rem 0 0;"><?= e(t('flow_filter')) ?>: <strong><?= e($flowLabel) ?></strong></p>
        </div>
    </div>
    <div class="kpi-grid">
        <div class="card kpi kpi-income">
            <span class="kpi-label"><?= e(t('income_kpi')) ?></span>
            <span class="kpi-value"><?= fmt_money($totalIncome) ?></span>
            <span class="kpi-sub"><?= e(t('currency')) ?> · <?= fmt_qty($totalInQty) ?> <?= e(t('units')) ?></span>
        </div>
        <div class="card kpi kpi-expense">
            <span class="kpi-label"><?= e(t('revenue_kpi')) ?></span>
            <span class="kpi-value"><?= fmt_money($totalExpense) ?></span>
            <span class="kpi-sub"><?= e(t('currency')) ?> · <?= fmt_qty($totalSalesQty) ?> <?= e(t('units')) ?></span>
        </div>
        <div class="card kpi kpi-balance">
            <span class="kpi-label"><?= e(t('cogs_kpi')) ?></span>
            <span class="kpi-value"><?= fmt_money($totalCost) ?></span>
            <span class="kpi-sub"><?= e(t('currency')) ?> <?= e(t('by_cost_unit')) ?></span>
        </div>
        <div class="card kpi <?= e($profitClass) ?>">
            <span class="kpi-label"><?= e(t('gross_profit_kpi')) ?></span>
            <span class="kpi-value"><?= fmt_money($totalProfit) ?></span>
            <span class="kpi-sub"><?= e(t('profitability')) ?> <?= number_format((float)$totalMargin, 1, ',', ' ') ?>%</span>
        </div>
    </div>
</section>

<div class="grid-2">
    <section class="card">
        <h2><?= e(t('chart_amounts')) ?></h2>
        <div class="chart-wrap"><canvas id="amountChart"></canvas></div>
    </section>
    <section class="card">
        <h2><?= e(t('chart_sales_qty')) ?></h2>
        <div class="chart-wrap"><canvas id="qtyChart"></canvas></div>
    </section>
</div>

<section class="card" style="margin-top:1.5rem;">
    <h2><?= e(t('calendar_report')) ?></h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th><a href="<?= e($sortUrl('date')) ?>" style="color:inherit;"><?= e(t('period')) ?><?= $sortMark('date') ?></a></th>
                    <th><a href="<?= e($sortUrl('income')) ?>" style="color:inherit;"><?= e(t('income_sum')) ?><?= $sortMark('income') ?></a></th>
                    <th><?= e(t('received_units')) ?></th>
                    <th><a href="<?= e($sortUrl('expense')) ?>" style="color:inherit;"><?= e(t('revenue_sum')) ?><?= $sortMark('expense') ?></a></th>
                    <th><a href="<?= e($sortUrl('sales_qty')) ?>" style="color:inherit;"><?= e(t('sold_units')) ?><?= $sortMark('sales_qty') ?></a></th>
                    <th><a href="<?= e($sortUrl('cost')) ?>" style="color:inherit;"><?= e(t('cogs_sum')) ?><?= $sortMark('cost') ?></a></th>
                    <th><a href="<?= e($sortUrl('profit')) ?>" style="color:inherit;"><?= e(t('profit_sum')) ?><?= $sortMark('profit') ?></a></th>
                    <th><a href="<?= e($sortUrl('margin')) ?>" style="color:inherit;"><?= e(t('margin_pct')) ?><?= $sortMark('margin') ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows): foreach ($rows as $row):
                    $periodLabel = $group === 'month' ? substr((string) $row['period'], 0, 7) : (string) $row['period'];
                    $pcls = $row['profit'] >= 0 ? 'money-pos' : 'money-neg';
                ?>
                <tr>
                    <td><?= e($periodLabel) ?></td>
                    <td><?= fmt_money($row['income']) ?></td>
                    <td><?= fmt_qty($row['in_qty']) ?></td>
                    <td><?= fmt_money($row['expense']) ?></td>
                    <td><?= fmt_qty($row['sales_qty']) ?></td>
                    <td><?= fmt_money($row['cost']) ?></td>
                    <td class="<?= $pcls ?>"><?= fmt_money($row['profit']) ?></td>
                    <td><?= number_format((float)$row['margin'], 1, ',', ' ') ?>%</td>
                </tr>
                <?php endforeach; ?>
                <tr style="font-weight:700; border-top:2px solid var(--border);">
                    <td><?= e(t('total')) ?></td>
                    <td><?= fmt_money($totalIncome) ?></td>
                    <td><?= fmt_qty($totalInQty) ?></td>
                    <td><?= fmt_money($totalExpense) ?></td>
                    <td><?= fmt_qty($totalSalesQty) ?></td>
                    <td><?= fmt_money($totalCost) ?></td>
                    <td class="<?= $totalProfit >= 0 ? 'money-pos' : 'money-neg' ?>"><?= fmt_money($totalProfit) ?></td>
                    <td><?= number_format((float)$totalMargin, 1, ',', ' ') ?>%</td>
                </tr>
                <?php else: ?>
                <tr><td colspan="8" style="text-align:center; color: var(--muted); padding: 2rem;"><?= e(t('no_data_period')) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
    .report-filters { display:flex; flex-direction:column; gap:1rem; }
    .filter-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; align-items:end; }
    .filter-actions { display:flex; gap:.75rem; flex-wrap:wrap; }
    .report-note { color: var(--muted); font-size: .9rem; margin: 0; }
    .report-section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
    .report-badge { display:inline-flex; align-items:center; padding:.35rem .7rem; border-radius:999px; border:1px solid var(--border); background:var(--surface); color:var(--muted); font-size:.9rem; white-space:nowrap; }
    .report-block { margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border); }
    .report-block:first-of-type { margin-top:0; padding-top:0; border-top:none; }
    .report-block-title { margin:0 0 .75rem; font-size:1rem; font-weight:700; color:var(--text); }
    .kpi-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
    .kpi-grid-compact { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
    .money-pos { color: var(--income); font-weight: 700; }
    .money-neg { color: var(--expense); font-weight: 700; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
const incomeData = <?= json_encode($incomeData) ?>;
const expenseData = <?= json_encode($expenseData) ?>;
const profitData = <?= json_encode($profitData) ?>;
const salesQtyData = <?= json_encode($salesQtyData) ?>;
const I18N = {
  income: <?= json_encode(t('chart_income'), JSON_UNESCAPED_UNICODE) ?>,
  revenue: <?= json_encode(t('chart_revenue'), JSON_UNESCAPED_UNICODE) ?>,
  profit: <?= json_encode(t('chart_profit'), JSON_UNESCAPED_UNICODE) ?>,
  soldQty: <?= json_encode(t('chart_sold_qty'), JSON_UNESCAPED_UNICODE) ?>,
  currency: <?= json_encode(t('currency'), JSON_UNESCAPED_UNICODE) ?>,
  units: <?= json_encode(t('units'), JSON_UNESCAPED_UNICODE) ?>
};
function css(v){ return getComputedStyle(document.documentElement).getPropertyValue(v).trim(); }
function chartColors(){ return { tick:css('--muted')||'#94a3b8', grid:css('--border')||'#334155', legend:css('--text')||'#f8fafc', accent:css('--accent')||'#38bdf8', expense:css('--expense')||'#ef4444', income:css('--income')||'#22c55e' }; }
let amountChart, qtyChart;
function fmtSum(v){ try { return new Intl.NumberFormat('ru-RU',{maximumFractionDigits:2}).format(v)+' '+I18N.currency; } catch(e){ return v+' '+I18N.currency; } }
function buildCharts(){
  const c=chartColors();
  const common={responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{labels:{color:c.legend}},tooltip:{callbacks:{label:(ctx)=> (ctx.dataset.label||'')+': '+fmtSum(ctx.parsed.y)}}},scales:{x:{ticks:{color:c.tick},grid:{color:c.grid}},y:{beginAtZero:true,ticks:{color:c.tick,callback:v=>fmtSum(v)},grid:{color:c.grid}}}};
  if(amountChart) amountChart.destroy(); if(qtyChart) qtyChart.destroy();
  amountChart=new Chart(document.getElementById('amountChart'),{type:'bar',data:{labels,datasets:[
    {label:I18N.income,data:incomeData,backgroundColor:c.accent+'88',borderColor:c.accent,borderWidth:1},
    {label:I18N.revenue,data:expenseData,backgroundColor:c.expense+'73',borderColor:c.expense,borderWidth:1},
    {label:I18N.profit,data:profitData,backgroundColor:c.income+'bf',borderColor:c.income,borderWidth:1}
  ]},options:common});
  qtyChart=new Chart(document.getElementById('qtyChart'),{type:'line',data:{labels,datasets:[{label:I18N.soldQty,data:salesQtyData,borderColor:c.accent,backgroundColor:c.accent+'26',fill:true,tension:.3,pointRadius:3}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:c.legend}}},scales:{x:{ticks:{color:c.tick},grid:{color:c.grid}},y:{beginAtZero:true,ticks:{color:c.tick,callback:v=>v+' '+I18N.units},grid:{color:c.grid}}}}});
}
document.addEventListener('DOMContentLoaded',()=>{ buildCharts(); new MutationObserver(()=>buildCharts()).observe(document.documentElement,{attributes:true,attributeFilter:['class']}); });
</script>
