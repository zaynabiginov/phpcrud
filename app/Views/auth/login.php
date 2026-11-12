<?php /** standalone login view */ $lang = app_lang(); $lockRemaining = max(0, (int)($lockRemaining ?? 0)); ?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(t('login_page_title')) ?></title>
    <base href="<?= e(base_href()) ?>">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>(function(){var t=localStorage.getItem('theme');if(t==='light')document.documentElement.classList.add('light');})();</script>
    <style>
        .auth-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;}
        .auth-card{width:100%;max-width:420px;background:var(--card);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid var(--border);border-radius:18px;box-shadow:var(--shadow-lg);padding:2rem 2rem 1.5rem;position:relative;overflow:hidden;}
        .auth-card::before{content:'';position:absolute;inset:-40% auto auto -20%;width:260px;height:260px;background:radial-gradient(circle,rgba(56,189,248,.22),transparent 70%);pointer-events:none;}
        .auth-card::after{content:'';position:absolute;inset:auto -20% -40% auto;width:220px;height:220px;background:radial-gradient(circle,rgba(129,140,248,.18),transparent 70%);pointer-events:none;}
        .auth-head{position:relative;text-align:center;margin-bottom:1.5rem;}
        .auth-mark{width:72px;height:72px;margin:0 auto .85rem;border-radius:0;display:flex;align-items:center;justify-content:center;background:transparent;box-shadow:none;border:none;overflow:visible;}
        .auth-mark img{width:58px;height:58px;display:block;}
        .auth-title{font-size:1.45rem;font-weight:800;letter-spacing:-.3px;margin:0;color:var(--text);}
        .auth-sub{margin:.35rem 0 0;color:var(--muted);font-size:.95rem;}
        .auth-form{position:relative;display:flex;flex-direction:column;gap:1rem;}
        .auth-field label{display:block;font-size:.85rem;font-weight:600;color:var(--muted);margin-bottom:.4rem;}
        .auth-field input{width:100%;padding:.8rem .95rem;background:var(--surface);border:1px solid var(--border);border-radius:12px;color:var(--text);font:inherit;font-size:.98rem;transition:border-color .18s,box-shadow .18s,background .18s;}
        .auth-field input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--focus-ring);}
        .auth-field input::placeholder{color:var(--muted);opacity:.6;}
        .auth-submit{margin-top:.25rem;margin-left:auto;width:auto;display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.65rem 1rem;border:none;border-radius:12px;font:inherit;font-weight:700;font-size:.95rem;color:#fff;cursor:pointer;background:linear-gradient(135deg,var(--accent),var(--accent-2));box-shadow:0 10px 22px rgba(56,189,248,.28);transition:filter .15s,transform .12s,box-shadow .15s;}
        .auth-submit:hover{filter:brightness(1.05);}
        .auth-submit:active{transform:translateY(1px);}
        .auth-submit:disabled{opacity:.55;cursor:not-allowed;filter:none;box-shadow:none;}
        .auth-submit svg{width:18px;height:18px;flex:0 0 auto;}
        .auth-error{position:relative;margin-bottom:1rem;padding:.75rem .95rem;border-radius:12px;border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.1);color:var(--expense);font-size:.92rem;}
        .auth-timer{position:relative;margin:-.25rem 0 1rem;padding:.6rem .85rem;border-radius:12px;border:1px solid rgba(245,158,11,.35);background:rgba(245,158,11,.12);color:#b45309;font-size:.92rem;text-align:center;font-weight:600;}
        :root:not(.light) .auth-timer{color:#fbbf24;}
        .auth-footer{position:relative;margin-top:1.25rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem;}
        .auth-footer .theme-toggle{width:44px;height:24px;}
        .lang-switcher{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem;border:1px solid var(--border);border-radius:10px;background:rgba(148,163,184,.06);}
        .lang-btn{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:30px;padding:0 .45rem;border-radius:8px;font-size:.78rem;font-weight:700;color:var(--muted);text-decoration:none;}
        .lang-btn:hover{color:var(--text);background:var(--hover-row);}
        .lang-btn.active{color:#fff;background:linear-gradient(135deg,var(--accent),var(--accent-2));}
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head">
            <div class="auth-mark"><img src="assets/favicon.svg" alt="<?= e(t('app_name')) ?>" width="58" height="58"></div>
            <h1 class="auth-title"><?= e(t('app_name')) ?></h1>
            <p class="auth-sub"><?= e(t('login_title')) ?></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="auth-error" id="authError"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($lockRemaining > 0): ?>
            <div class="auth-timer" id="authTimer" data-remaining="<?= (int)$lockRemaining ?>" data-msg="<?= e(t('login_retry_in')) ?>"><?= e(sprintf(t('login_retry_in'), (int)$lockRemaining)) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= url('login') ?>" class="auth-form" id="loginForm" novalidate>
            <div class="auth-field">
                <label for="username"><?= e(t('username')) ?></label>
                <input type="text" id="username" name="username" autocomplete="username" placeholder="<?= e(t('enter_username')) ?>" required <?= $lockRemaining > 0 ? 'disabled' : 'autofocus' ?>>
            </div>
            <div class="auth-field">
                <label for="password"><?= e(t('password')) ?></label>
                <input type="password" id="password" name="password" autocomplete="current-password" placeholder="<?= e(t('enter_password')) ?>" required <?= $lockRemaining > 0 ? 'disabled' : '' ?>>
            </div>
            <button type="submit" class="auth-submit" id="loginSubmit" <?= $lockRemaining > 0 ? 'disabled' : '' ?>><?= e(t('sign_in')) ?> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></button>
        </form>

        <div class="auth-footer">
            <div class="lang-switcher" role="group" aria-label="<?= e(t('lang')) ?>">
                <?php foreach (['ru'=>'RU','uz'=>'UZ','en'=>'EN'] as $code=>$label): ?>
                    <a href="?lang=<?= $code ?>" class="lang-btn <?= $lang===$code?'active':'' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
            <button type="button" class="theme-toggle" id="themeToggle" role="switch" aria-checked="false" title="<?= e(t('theme')) ?>" aria-label="<?= e(t('theme')) ?>" data-label-light="<?= e(t('theme_light')) ?>" data-label-dark="<?= e(t('theme_dark')) ?>"><span class="toggle-track" aria-hidden="true"><span class="ti ti-moon">🌙</span><span class="ti ti-sun">☀️</span></span><span class="toggle-thumb"></span></button>
        </div>
    </div>
</div>
<script src="assets/js/script.js"></script>
<script>
(function(){
  var timer = document.getElementById('authTimer');
  if (!timer) return;
  var form = document.getElementById('loginForm');
  var fields = form ? form.querySelectorAll('input, button[type="submit"]') : [];
  var remaining = parseInt(timer.getAttribute('data-remaining') || '0', 10);
  if (isNaN(remaining) || remaining <= 0) return;
  var msgTpl = timer.getAttribute('data-msg') || '';
  function setDisabled(disabled){
    fields.forEach(function(el){ el.disabled = disabled; });
  }
  function fmt(sec){
    var m = Math.floor(sec / 60);
    var s = sec % 60;
    return m + ':' + (s < 10 ? '0' : '') + s;
  }
  function tick(){
    if (remaining <= 0){
      timer.textContent = '';
      timer.style.display = 'none';
      setDisabled(false);
      var err = document.getElementById('authError');
      if (err) err.style.display = 'none';
      var u = document.getElementById('username');
      if (u) u.focus();
      return;
    }
    if (msgTpl && msgTpl.indexOf('%') !== -1) {
      try { timer.textContent = msgTpl.replace(/%[sd]/, remaining); } catch(e){ timer.textContent = remaining; }
    } else {
      timer.textContent = remaining + 's';
    }
    remaining--;
    setTimeout(tick, 1000);
  }
  setDisabled(true);
  tick();
})();
</script>
</body>
</html>