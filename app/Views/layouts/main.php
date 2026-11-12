<?php /** @var string $content */ $lang = app_lang(); $usd = get_usd_rate(); $today = date('Y-m-d'); ?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? t('app_name')) ?></title>
    <base href="<?= e(base_href()) ?>">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>(function(){var t=localStorage.getItem('theme');if(t==='light')document.documentElement.classList.add('light');var s=localStorage.getItem('sidebar');if(s==='collapsed')document.documentElement.classList.add('sidebar-collapsed');})();</script>
    <?= $headExtra ?? '' ?>
</head>
<body>
<div class="app-shell">
    <?php $active = $active ?? ''; include __DIR__ . '/../partials/nav.php'; ?>
    <div class="app-main">
        <header class="topbar">
            <button type="button" class="icon-btn sidebar-toggle" id="sidebarToggle" aria-label="<?= e(t('menu')) ?>" title="<?= e(t('menu')) ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="topbar-title"><?= e($title ?? t('app_name')) ?></div>
            <div class="topbar-actions">
                <div class="topbar-info" aria-label="info">
                    <div class="tb-chip tb-date" title="<?= e(t('today')) ?>">
                        <span class="tb-k"><?= e($today) ?></span>
                        <span class="tb-v" id="topClock">--:--:-- --</span>
                    </div>
                    <div class="tb-chip tb-usd" title="<?= e(t('usd_rate')) ?> · <?= e(t('cbu_uz')) ?>">
                        <span class="tb-k"><?= e(t('usd_rate')) ?></span>
                        <?php if (!empty($usd['rate'])): $diff = (float)str_replace(',', '.', (string)($usd['diff'] ?? '0')); ?>
                            <span class="tb-v"><?= e(number_format((float)str_replace(',', '.', (string)$usd['rate']), 2, ',', ' ')) ?> <small><?= e(t('currency')) ?></small><?php if ($usd['diff'] !== null && $usd['diff'] !== ''): ?> <span class="rate-diff <?= $diff > 0 ? 'up' : ($diff < 0 ? 'down' : '') ?>"><?= $diff > 0 ? '▲' : ($diff < 0 ? '▼' : '') ?> <?= e((string)$usd['diff']) ?></span><?php endif; ?></span>
                        <?php else: ?>
                            <span class="tb-v muted"><?= e(t('rate_unavailable')) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="tb-chip tb-weather" title="<?= e(t('weather')) ?>">
                        <span class="tb-k"><?= e(t('weather')) ?></span>
                        <span class="tb-v"><span class="tb-wi" id="topWeatherIcon" aria-hidden="true"></span><span id="topWeatherTemp"><?= e(t('weather_loading')) ?></span></span>
                        <span class="tb-s" id="topWeatherMeta"></span>
                    </div>
                </div>
                <div class="lang-switcher" role="group" aria-label="<?= e(t('lang')) ?>">
                    <?php foreach (['ru'=>'RU','uz'=>'UZ','en'=>'EN'] as $code=>$label): ?>
                        <a href="?lang=<?= $code ?>" class="lang-btn <?= $lang===$code?'active':'' ?>" title="<?= $label ?>"><?= $label ?></a>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="theme-toggle" id="themeToggle" role="switch" aria-checked="false" title="<?= e(t('theme')) ?>" aria-label="<?= e(t('theme')) ?>" data-label-light="<?= e(t('theme_light')) ?>" data-label-dark="<?= e(t('theme_dark')) ?>"><span class="toggle-track" aria-hidden="true"><span class="ti ti-moon">🌙</span><span class="ti ti-sun">☀️</span></span><span class="toggle-thumb"></span></button>
            </div>
        </header>
        <main class="page-content">
            <?= $content ?>
        </main>
    </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<script src="assets/js/script.js"></script>
<script>
(function(){
  const clockEl = document.getElementById('topClock');
  function pad(n){ return n < 10 ? '0'+n : ''+n; }
  function tick(){
    if (!clockEl) return;
    const d = new Date(); let h=d.getHours(); const m=d.getMinutes(); const s=d.getSeconds();
    const ampm = h >= 12 ? 'PM' : 'AM'; h = h % 12; if (h === 0) h = 12;
    clockEl.textContent = pad(h)+':'+pad(m)+':'+pad(s)+' '+ampm;
  }
  if (clockEl){ tick(); setInterval(tick, 1000); }

  const tempEl = document.getElementById('topWeatherTemp');
  const metaEl = document.getElementById('topWeatherMeta');
  const iconEl = document.getElementById('topWeatherIcon');
  if (!tempEl || !metaEl) return;
  const L = {
    denied: <?= json_encode(t('weather_denied'), JSON_UNESCAPED_UNICODE) ?>,
    unavailable: <?= json_encode(t('weather_unavailable'), JSON_UNESCAPED_UNICODE) ?>,
    wind: <?= json_encode(t('wind'), JSON_UNESCAPED_UNICODE) ?>,
    mps: <?= json_encode(t('mps'), JSON_UNESCAPED_UNICODE) ?>,
    codes: {
      clear: <?= json_encode(t('weather_clear'), JSON_UNESCAPED_UNICODE) ?>,
      partly: <?= json_encode(t('weather_partly'), JSON_UNESCAPED_UNICODE) ?>,
      cloudy: <?= json_encode(t('weather_cloudy'), JSON_UNESCAPED_UNICODE) ?>,
      fog: <?= json_encode(t('weather_fog'), JSON_UNESCAPED_UNICODE) ?>,
      drizzle: <?= json_encode(t('weather_drizzle'), JSON_UNESCAPED_UNICODE) ?>,
      rain: <?= json_encode(t('weather_rain'), JSON_UNESCAPED_UNICODE) ?>,
      snow: <?= json_encode(t('weather_snow'), JSON_UNESCAPED_UNICODE) ?>,
      showers: <?= json_encode(t('weather_showers'), JSON_UNESCAPED_UNICODE) ?>,
      thunder: <?= json_encode(t('weather_thunder'), JSON_UNESCAPED_UNICODE) ?>,
      unknown: <?= json_encode(t('weather_unknown'), JSON_UNESCAPED_UNICODE) ?>
    }
  };
  function label(code){
    const c = Number(code);
    if (c === 0) return L.codes.clear; if (c === 1 || c === 2) return L.codes.partly; if (c === 3) return L.codes.cloudy;
    if (c === 45 || c === 48) return L.codes.fog; if (c >= 51 && c <= 57) return L.codes.drizzle;
    if (c >= 61 && c <= 67) return L.codes.rain; if (c >= 71 && c <= 77) return L.codes.snow;
    if (c >= 80 && c <= 82) return L.codes.showers; if (c >= 85 && c <= 86) return L.codes.snow; if (c >= 95) return L.codes.thunder;
    return L.codes.unknown;
  }
  function wicon(code){
    const c = Number(code);
    if (c === 0) return '☀️';
    if (c === 1) return '🌤️';
    if (c === 2) return '⛅';
    if (c === 3) return '☁️';
    if (c === 45 || c === 48) return '🌫️';
    if (c >= 51 && c <= 57) return '🌦️';
    if (c >= 61 && c <= 67) return '🌧️';
    if (c >= 71 && c <= 77) return '🌨️';
    if (c >= 80 && c <= 82) return '🌧️';
    if (c >= 85 && c <= 86) return '🌨️';
    if (c >= 95) return '⛈️';
    return '';
  }
  function setFail(msg){ tempEl.textContent = msg; tempEl.classList.add('muted'); if (metaEl) metaEl.textContent = ''; if (iconEl) iconEl.textContent = ''; }
  if (!navigator.geolocation) { setFail(L.unavailable); return; }
  navigator.geolocation.getCurrentPosition(function(pos){
    const url = 'https://api.open-meteo.com/v1/forecast?latitude='+encodeURIComponent(pos.coords.latitude)+'&longitude='+encodeURIComponent(pos.coords.longitude)+'&current=temperature_2m,weather_code,wind_speed_10m&wind_speed_unit=ms';
    fetch(url).then(r=>r.json()).then(data=>{
      if (!data || !data.current) { setFail(L.unavailable); return; }
      const t=data.current.temperature_2m, w=data.current.wind_speed_10m, code=data.current.weather_code;
      tempEl.classList.remove('muted');
      if (iconEl) iconEl.textContent = wicon(code);
      tempEl.textContent = (t!==undefined&&t!==null?Math.round(Number(t))+'°C':'—');
      metaEl.textContent = label(code)+(w!==undefined&&w!==null?(' · '+Math.round(Number(w)*10)/10+' '+L.mps):'');
    }).catch(()=>setFail(L.unavailable));
  }, function(){ setFail(L.denied); }, { enableHighAccuracy:false, timeout:8000, maximumAge:600000 });
})();
</script>
<?= $scripts ?? '' ?>
</body>
</html>