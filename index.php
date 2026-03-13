<?php
// ─── Security Headers ────────────────────────────────────────────────────────
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com; font-src https://fonts.gstatic.com; img-src 'self' data: https://*.tile.openstreetmap.org;");

// ─── Timezone ─────────────────────────────────────────────────────────────────
if (!ini_get('date.timezone')) {
    date_default_timezone_set('GMT');
}

// ─── Session-Based Rate Limiting ─────────────────────────────────────────────
session_start();
$now = time();
if (!isset($_SESSION['rl_count']) || ($now - $_SESSION['rl_start']) > 60) {
    $_SESSION['rl_count'] = 0;
    $_SESSION['rl_start'] = $now;
}
$_SESSION['rl_count']++;
if ($_SESSION['rl_count'] > 30) {
    http_response_code(429);
    die('<h2 style="font-family:sans-serif;text-align:center;margin-top:4rem;color:#ef4444;">⚠️ Too many requests. Please wait a moment and try again.</h2>');
}

// ─── Cache Setup ─────────────────────────────────────────────────────────────
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

// ─── Input Validation ────────────────────────────────────────────────────────
$rawInput = '';
$inputError = '';
if (!empty($_GET['ip'])) {
    $rawInput = trim(strip_tags($_GET['ip']));
    // Remove protocol prefixes and trailing slashes
    $rawInput = preg_replace('#^https?://#i', '', $rawInput);
    $rawInput = rtrim($rawInput, '/');

    // Allow valid IPv4, IPv6, or valid hostname/domain
    $isValidIP     = filter_var($rawInput, FILTER_VALIDATE_IP) !== false;
    $isValidDomain = preg_match('/^(([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,})$/', $rawInput);

    if (!$isValidIP && !$isValidDomain) {
        $inputError = "⚠️ Invalid IP address or domain name.";
        $rawInput   = '';
    }
}

$ipppir = $rawInput ?: $_SERVER['REMOTE_ADDR'];
$isOwn  = empty($rawInput); // true when showing visitor's own IP

// ─── Fetch Geolocation Data ──────────────────────────────────────────────────
function fetchGeoData(string $ip, string $cacheDir): ?array {
    $cacheFile = $cacheDir . '/' . md5($ip) . '.json';
    $cacheTTL  = 300; // 5 minutes

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if ($cached && $cached['status'] === 'success') {
            return $cached;
        }
    }

    $url  = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=status,message,country,countryCode,regionName,region,city,zip,lat,lon,timezone,isp,org,as,query';
    $opts = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $raw  = @file_get_contents($url, false, $opts);
    if ($raw === false) return null;

    $data = json_decode($raw, true);
    if (!$data) return null;

    if ($data['status'] === 'success') {
        @file_put_contents($cacheFile, $raw, LOCK_EX);
    }
    return $data;
}

$queipy   = fetchGeoData($ipppir, $cacheDir);
$geoError = '';

if (!$queipy || $queipy['status'] !== 'success') {
    $geoError = $queipy['message'] ?? 'Unable to get location data.';
}

// Helper: safely echo a field
function g($key): string {
    global $queipy;
    return htmlspecialchars($queipy[$key] ?? '', ENT_QUOTES, 'UTF-8');
}

$countryCode = strtolower(htmlspecialchars($queipy['countryCode'] ?? 'us', ENT_QUOTES, 'UTF-8'));
$lat         = isset($queipy['lat']) ? (float)$queipy['lat'] : 0;
$lon         = isset($queipy['lon']) ? (float)$queipy['lon'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Free IP Geolocation Lookup tool — find country, city, ISP, timezone and more.">
    <title>IP Lookup — Kariya Host</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">

    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>

<!-- ░░ BACKGROUND ORBS ░░ -->
<div class="bg-orb orb1"></div>
<div class="bg-orb orb2"></div>
<div class="bg-orb orb3"></div>

<!-- ░░ NAVBAR ░░ -->
<nav class="navbar">
    <a class="navbar-brand" href="index.php">
        <img src="logo.png" alt="Kariya Host" width="90" height="35">
    </a>
    <form class="search-form" action="index.php" method="GET">
        <div class="search-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input
                type="text"
                name="ip"
                id="searchInput"
                class="search-input"
                value="<?= htmlspecialchars($rawInput, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Enter IP or domain…"
                autocomplete="off"
                spellcheck="false"
            >
            <?php if ($rawInput): ?>
            <a href="index.php" class="search-clear" title="Clear">✕</a>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn-search">Lookup</button>
    </form>
</nav>

<!-- ░░ MAIN ░░ -->
<main class="main">

    <?php if ($inputError): ?>
    <div class="alert-error fade-in">
        <?= htmlspecialchars($inputError, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php elseif ($geoError): ?>
    <div class="alert-error fade-in">
        ⚠️ <?= htmlspecialchars($geoError, ENT_QUOTES, 'UTF-8') ?>
        <a href="index.php">Try again</a>
    </div>
    <?php else: ?>

    <!-- ── Hero Card ── -->
    <div class="hero-card fade-in">

        <?php if ($isOwn): ?>
        <div class="own-badge">📡 Your current location</div>
        <?php endif; ?>

        <div class="hero-top">
            <div class="flag-wrap">
                <img src="png/<?= $countryCode ?>.png" alt="<?= g('country') ?> flag" class="flag-img">
            </div>
            <div class="hero-info">
                <h1 class="country-name"><?= g('country') ?></h1>
                <p class="city-name"><?= g('city') ?><?= $queipy['regionName'] ? ', ' . g('regionName') : '' ?></p>
                <div class="ip-display">
                    <span id="ipValue"><?= g('query') ?></span>
                    <button class="btn-copy" onclick="copyIP()" title="Copy IP">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        <span id="copyLabel">Copy</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div id="map"></div>

        <!-- Data Grid -->
        <div class="data-grid">
            <div class="data-item">
                <span class="data-label">Country Code</span>
                <span class="data-value"><?= g('countryCode') ?></span>
            </div>
            <div class="data-item">
                <span class="data-label">Region</span>
                <span class="data-value"><?= g('regionName') ?> <?= $queipy['region'] ? '(' . g('region') . ')' : '' ?></span>
            </div>
            <div class="data-item">
                <span class="data-label">City</span>
                <span class="data-value"><?= g('city') ?></span>
            </div>
            <div class="data-item">
                <span class="data-label">ZIP Code</span>
                <span class="data-value"><?= g('zip') ?: '—' ?></span>
            </div>
            <div class="data-item">
                <span class="data-label">Latitude</span>
                <span class="data-value"><?= g('lat') ?></span>
            </div>
            <div class="data-item">
                <span class="data-label">Longitude</span>
                <span class="data-value"><?= g('lon') ?></span>
            </div>
            <div class="data-item">
                <span class="data-label">Timezone</span>
                <span class="data-value"><?= g('timezone') ?></span>
            </div>
            <div class="data-item">
                <span class="data-label">ISP</span>
                <span class="data-value"><?= g('isp') ?></span>
            </div>
            <div class="data-item full-width">
                <span class="data-label">Organization</span>
                <span class="data-value"><?= g('org') ?></span>
            </div>
            <div class="data-item full-width">
                <span class="data-label">AS Number / Name</span>
                <span class="data-value"><?= g('as') ?></span>
            </div>
        </div>

    </div><!-- /.hero-card -->

    <?php endif; ?>
</main>

<!-- ░░ FOOTER ░░ -->
<footer class="footer">
    <p>© 2014–<?= date('Y') ?> <a href="https://github.com/mrghozzi/kariya_ip" target="_blank" rel="noopener noreferrer">Kariya IP</a> &nbsp;·&nbsp; Data by <a href="https://ip-api.com" target="_blank" rel="noopener noreferrer">ip-api.com</a></p>
</footer>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
// ── Leaflet Map ───────────────────────────────────────────────────────────────
<?php if (!$geoError && !$inputError && $lat && $lon): ?>
(function() {
    var lat = <?= $lat ?>;
    var lon = <?= $lon ?>;
    var city = <?= json_encode($queipy['city'] ?? '') ?>;
    var country = <?= json_encode($queipy['country'] ?? '') ?>;

    var map = L.map('map', { zoomControl: true, scrollWheelZoom: false }).setView([lat, lon], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);

    var icon = L.divIcon({
        className: '',
        html: '<div class="map-pin"></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    L.marker([lat, lon], { icon: icon })
        .addTo(map)
        .bindPopup('<strong>' + city + '</strong><br>' + country)
        .openPopup();
})();
<?php else: ?>
document.getElementById('map').style.display = 'none';
<?php endif; ?>

// ── Copy IP ───────────────────────────────────────────────────────────────────
function copyIP() {
    var ip = document.getElementById('ipValue').textContent;
    navigator.clipboard.writeText(ip).then(function() {
        var label = document.getElementById('copyLabel');
        label.textContent = 'Copied!';
        setTimeout(function() { label.textContent = 'Copy'; }, 2000);
    });
}
</script>

</body>
</html>
