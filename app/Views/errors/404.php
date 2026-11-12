<?php
declare(strict_types=1);
/** @var string|null $path */
$lang = function_exists('app_lang') ? app_lang() : 'ru';
$home = function_exists('url') ? url('home') : '/home';
?>
<!DOCTYPE html>
<html lang="<?= function_exists('e') ? e($lang) : 'ru' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — <?= function_exists('t') ? e(t('page_not_found')) : 'Not Found' ?></title>
    <base href="<?= function_exists('base_href') ? e(base_href()) : '/' ?>">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>(function(){var t=localStorage.getItem('theme');if(t==='light')document.documentElement.classList.add('light');})();</script>
    <style>
        .nf-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;position:relative;overflow:hidden;}
        .nf-wrap::before,.nf-wrap::after{content:'';position:absolute;border-radius:50%;filter:blur(10px);pointer-events:none;opacity:.55;}
        .nf-wrap::before{width:420px;height:420px;left:-120px;top:-120px;background:radial-gradient(circle,rgba(56,189,248,.28),transparent 70%);}
        .nf-wrap::after{width:360px;height:360px;right:-100px;bottom:-120px;background:radial-gradient(circle,rgba(129,140,248,.24),transparent 70%);}
        .nf-card{position:relative;z-index:1;width:100%;max-width:520px;text-align:center;background:var(--card);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid var(--border);border-radius:22px;box-shadow:var(--shadow-lg);padding:2.5rem 2rem 2.25rem;}
        .nf-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .7rem;border-radius:999px;border:1px solid var(--border);background:rgba(148,163,184,.08);color:var(--muted);font-size:.8rem;font-weight:600;margin-bottom:1rem;}
        .nf-code{font-size:clamp(4.5rem,14vw,7.5rem);line-height:1;font-weight:900;letter-spacing:-.04em;margin:0 0 .25rem;background:linear-gradient(135deg,var(--accent),var(--accent-2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;color:transparent;}
        .nf-title{font-size:1.35rem;font-weight:800;color:var(--text);margin:0 0 .5rem;letter-spacing:-.2px;}
        .nf-desc{color:var(--muted);font-size:.98rem;line-height:1.6;margin:0 auto 1.6rem;max-width:36ch;}
        .nf-actions{display:flex;align-items:center;justify-content:center;gap:.75rem;flex-wrap:wrap;}
        .nf-btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.75rem 1.2rem;border-radius:12px;font:inherit;font-weight:700;font-size:.95rem;text-decoration:none;cursor:pointer;transition:filter .15s,transform .12s,box-shadow .15s,border-color .15s,background .15s;}
        .nf-btn:active{transform:translateY(1px);}
        .nf-btn-primary{color:#fff;background:linear-gradient(135deg,var(--accent),var(--accent-2));box-shadow:0 10px 22px rgba(56,189,248,.28);border:none;}
        .nf-btn-primary:hover{filter:brightness(1.06);color:#fff;}
        .nf-btn-ghost{color:var(--text);border:1px solid var(--border);background:var(--surface);}
        .nf-btn-ghost:hover{border-color:var(--accent);color:var(--accent);background:var(--hover-row);}
        .nf-icon{width:18px;height:18px;flex:0 0 auto;}
        .nf-hint{margin-top:1.4rem;font-size:.82rem;color:var(--muted);}
    </style>
</head>
<body>
<div class="nf-wrap">
    <div class="nf-card">
        <div class="nf-badge">
            <svg class="nf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            <?= function_exists('t') ? e(t('app_name')) : 'Magazine' ?>
        </div>
        <div class="nf-code">404</div>
        <h1 class="nf-title"><?= function_exists('t') ? e(t('page_not_found')) : 'Страница не найдена' ?></h1>
        <p class="nf-desc"><?= function_exists('t') ? e(t('page_not_found_desc')) : 'Запрашиваемая страница не существует, была перемещена или адрес указан неверно.' ?></p>
        <div class="nf-actions">
            <a class="nf-btn nf-btn-primary" href="<?= function_exists('e') ? e($home) : '/home' ?>">
                <svg class="nf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5L12 3l9 7.5"/><path d="M5.5 9.5V20h13V9.5"/></svg>
                <?= function_exists('t') ? e(t('go_home')) : 'На главную' ?>
            </a>
            <button class="nf-btn nf-btn-ghost" type="button" onclick="if(history.length>1){history.back()}else{location.href='<?= function_exists('e') ? e($home) : '/home/' ?>'}">
                <svg class="nf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                <?= function_exists('t') ? e(t('back')) : 'Назад' ?>
            </button>
        </div>
        <div class="nf-hint"><?= function_exists('t') ? e(t('error_404_hint')) : 'Проверьте адрес или вернитесь на главную страницу.' ?></div>
    </div>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>
