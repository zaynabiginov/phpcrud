<?php
$u = current_user();
$active = $active ?? '';
$isAdmin = $u && ($u['role'] ?? '') === 'admin';
$roleLabel = $isAdmin ? t('role_admin') : t('role_shop');
$items = [];
if ($u) $items[] = ['key'=>'index','url'=>url('home'),'label'=>t('home'),'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10.5L12 3l9 7.5"/><path d="M5.5 9.5V20h13V9.5"/></svg>'];
$items[] = ['key'=>'sell','url'=>url('sales'),'label'=>t('sales'),'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 5h2l2.2 10h9.6L19 8H7.2"/><circle cx="9" cy="19" r="1.5"/><circle cx="16" cy="19" r="1.5"/></svg>'];
$items[] = ['key'=>'stock','url'=>url('warehouse'),'label'=>t('warehouse'),'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v10l9 4 9-4V7"/><path d="M12 11v10"/></svg>'];
if ($isAdmin) $items[] = ['key'=>'report','url'=>url('report'),'label'=>t('report'),'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19h16"/><rect x="6" y="10" width="3" height="6" rx="1"/><rect x="11" y="7" width="3" height="9" rx="1"/><rect x="16" y="4" width="3" height="12" rx="1"/></svg>'];
if ($isAdmin) $items[] = ['key'=>'users','url'=>url('users'),'label'=>t('users'),'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-7A3.5 3.5 0 0 0 2 17.5V19"/><circle cx="9" cy="8" r="3.5"/><path d="M22 19v-1.2a3.3 3.3 0 0 0-2.5-3.2"/><path d="M15.5 4.6a3.5 3.5 0 0 1 0 6.8"/></svg>'];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="<?= e(url(home_url())) ?>" class="brand-mark" title="<?= e(t('app_name')) ?>">
            <span class="brand-icon"><img src="assets/favicon.svg" alt="" width="34" height="34"></span>
            <span class="brand-text"><?= e(t('app_name')) ?></span>
        </a>
    </div>
    <nav class="sidebar-nav" aria-label="<?= e(t('main_menu')) ?>">
        <?php foreach ($items as $it): ?>
        <a href="<?= e($it['url']) ?>" class="nav-item <?= $active === $it['key'] ? 'active' : '' ?>" title="<?= e($it['label']) ?>">
            <span class="nav-icon"><?= $it['icon'] ?></span>
            <span class="nav-label"><?= e($it['label']) ?></span>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <?php if ($u): ?>
        <div class="sidebar-user" title="<?= e($u['username']) ?> (<?= e($roleLabel) ?>)">
            <div class="user-avatar"><?= e(mb_strtoupper(mb_substr((string)$u['username'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
            <div class="user-meta">
                <div class="user-name"><?= e($u['username']) ?></div>
                <div class="user-role"><?= e($roleLabel) ?></div>
            </div>
        </div>
        <a href="<?= e(url('logout')) ?>" class="nav-item nav-logout" title="<?= e(t('logout')) ?>">
            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 3h4v18h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg></span>
            <span class="nav-label"><?= e(t('logout')) ?></span>
        </a>
        <?php endif; ?>
    </div>
</aside>