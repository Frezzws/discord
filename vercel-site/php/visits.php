<?php
/**
 * Log listesi: Önce TXT'den okunur (txt'den loga). İki yerde de kayıt olsun diye JSON da taranır.
 */
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Cache-Control: no-store');

$dirs = array(__DIR__ . '/../data', __DIR__ . '/data');
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
