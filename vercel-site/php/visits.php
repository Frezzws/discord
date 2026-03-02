<?php
/**
 * Log listesi: Önce TXT'den okunur (txt'den loga). İki yerde de kayıt olsun diye JSON da taranır.
 * ?record=1 ile çağrılırsa bu isteği yapan ziyaretçi de önce kaydedilir (kayıt yok kalmasın).
 */
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Cache-Control: no-store');

$dirs = array(__DIR__ . '/../data', __DIR__ . '/data');
$dataDir = null;
foreach ($dirs as $d) {
    $d = rtrim($d, '/');
    if (!is_dir($d)) @mkdir($d, 0755, true);
    if (is_dir($d) && is_writable($d)) { $dataDir = $d; break; }
}
if ($dataDir && isset($_GET['record']) && $_GET['record'] === '1') {
    $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
    $time = date('Y-m-d H:i:s');
    $txtFile = $dataDir . '/visits.txt';
    @file_put_contents($txtFile, $ip . ' | ' . str_replace(array("\r", "\n"), ' ', $ua) . ' | ' . $time . "\n", FILE_APPEND | LOCK_EX);
    $jsonFile = $dataDir . '/visits.json';
    $list = array();
    if (file_exists($jsonFile) && is_readable($jsonFile)) {
        $raw = @file_get_contents($jsonFile);
        if ($raw) $list = json_decode($raw, true);
        if (!is_array($list)) $list = array();
    }
    $list[] = array('ip' => $ip, 'user_agent' => $ua, 'time' => $time);
    @file_put_contents($jsonFile, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$list = array();

// 1) Önce tüm visits.txt dosyalarını oku (TXT = ana kaynak, sitede çıksın)
foreach ($dirs as $d) {
    $d = rtrim($d, '/');
    $txtFile = $d . '/visits.txt';
    if (!file_exists($txtFile) || !is_readable($txtFile)) continue;
    $lines = @file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) continue;
    foreach ($lines as $line) {
        $parts = array_map('trim', explode('|', $line, 3));
        if (empty($parts[0])) continue;
        $list[] = array(
            'ip' => $parts[0],
            'user_agent' => isset($parts[1]) ? $parts[1] : '',
            'time' => isset($parts[2]) ? $parts[2] : ''
        );
    }
}

// 2) JSON'dan da ekle (aynı IP+time yoksa), böylece 2 yerde de olsun
foreach ($dirs as $d) {
    $d = rtrim($d, '/');
    $jsonFile = $d . '/visits.json';
    if (!file_exists($jsonFile) || !is_readable($jsonFile)) continue;
    $raw = @file_get_contents($jsonFile);
    if (!$raw) continue;
    $jsonList = json_decode($raw, true);
    if (!is_array($jsonList)) continue;
    $have = array();
    foreach ($list as $e) $have[$e['ip'] . '|' . ($e['time'] ?? '')] = true;
    foreach ($jsonList as $e) {
        $k = ($e['ip'] ?? '') . '|' . ($e['time'] ?? '');
        if (!empty($e['ip']) && empty($have[$k])) {
            $list[] = array('ip' => $e['ip'], 'user_agent' => $e['user_agent'] ?? '', 'time' => $e['time'] ?? '');
            $have[$k] = true;
        }
    }
}

// Zamanı parse edip en yeni önce sırala (TXT'de genelde en alttaki en yeni)
usort($list, function ($a, $b) {
    $ta = strtotime($a['time'] ?? '0');
    $tb = strtotime($b['time'] ?? '0');
    return $tb - $ta;
});

echo json_encode($list);
