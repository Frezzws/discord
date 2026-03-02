<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$ip = isset($_GET['ip']) ? trim($_GET['ip']) : '';
$file = __DIR__ . '/../data/visits.json';
$list = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
$entry = null;
foreach (array_reverse($list) as $e) {
    if (($e['ip'] ?? '') === $ip) { $entry = $e; break; }
}
$ua = $entry['user_agent'] ?? '';
$browser = 'Bilinmiyor';
if (preg_match('/Chrome\/[0-9.]+/', $ua) && !preg_match('/Edg/', $ua)) $browser = 'Chrome';
elseif (preg_match('/Firefox\/[0-9.]+/', $ua)) $browser = 'Firefox';
elseif (preg_match('/Edg\/[0-9.]+/', $ua)) $browser = 'Edge';
elseif (preg_match('/Safari\/[0-9.]+/', $ua) && !preg_match('/Chrome/', $ua)) $browser = 'Safari';
elseif (preg_match('/OPR\/[0-9.]+/', $ua)) $browser = 'Opera';
$os = 'Bilinmiyor';
if (preg_match('/Windows NT [0-9.]+/', $ua)) $os = 'Windows';
elseif (preg_match('/Mac OS X/', $ua)) $os = 'macOS';
elseif (preg_match('/Android [0-9.]+/', $ua)) $os = 'Android';
elseif (preg_match('/iPhone|iPad/', $ua)) $os = 'iOS';
elseif (preg_match('/Linux/', $ua)) $os = 'Linux';
$konum = '—';
if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
    $geo = @file_get_contents('http://ip-api.com/json/' . urlencode($ip) . '?fields=country,city,regionName');
    if ($geo) {
        $g = json_decode($geo, true);
        if ($g && isset($g['country'])) $konum = ($g['city'] ?? '') . ', ' . ($g['country'] ?? '');
    }
}
echo json_encode([
    'ip' => $ip,
    'browser' => $browser,
    'os' => $os,
    'konum' => $konum,
    'ekran_karti' => '—',
    'user_agent' => $ua,
    'time' => $entry['time'] ?? ''
]);
?>
