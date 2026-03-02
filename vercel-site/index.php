<?php
/**
 * Ana sayfa: Her girişte IP'yi log'a yazar, sonra index.html içeriğini gösterir.
 * Sunucuda varsayılan girişi index.php yaparsan her ziyaret otomatik log'a düşer.
 */
$dir = __DIR__ . '/data';
if (!is_dir($dir)) @mkdir($dir, 0755, true);

$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0'));
if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
$time = date('Y-m-d H:i:s');

// 1) Önce TXT'ye kaydet (txt ye kaydolsun, txt'den loga)
$txtFile = $dir . '/visits.txt';
$line = $ip . ' | ' . str_replace(array("\r", "\n"), ' ', $ua) . ' | ' . $time . "\n";
@file_put_contents($txtFile, $line, FILE_APPEND | LOCK_EX);

// 2) JSON'a da yaz (2 yerde de olsun)
$jsonFile = $dir . '/visits.json';
$list = array();
if (file_exists($jsonFile) && is_readable($jsonFile)) {
    $raw = @file_get_contents($jsonFile);
    if ($raw) $list = json_decode($raw, true);
    if (!is_array($list)) $list = array();
}
$list[] = array('ip' => $ip, 'user_agent' => $ua, 'time' => $time);
@file_put_contents($jsonFile, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$htmlFile = __DIR__ . '/index.html';
if (file_exists($htmlFile) && is_readable($htmlFile)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($htmlFile);
} else {
    header('Content-Type: text/plain');
    echo 'index.html bulunamadi';
}
?>
