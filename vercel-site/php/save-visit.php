<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Cache-Control: no-store');

$dir = __DIR__ . '/../data';
if (!is_dir($dir) || !is_writable($dir)) {
    $dir = __DIR__ . '/data';
}
if (!is_dir($dir)) @mkdir($dir, 0755, true);
$file = rtrim($dir, '/') . '/visits.json';

$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0'));
if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
$time = date('c');

$list = [];
if (file_exists($file) && is_readable($file)) {
    $raw = @file_get_contents($file);
    if ($raw) $list = json_decode($raw, true);
    if (!is_array($list)) $list = [];
}
$list[] = array('ip' => $ip, 'user_agent' => $ua, 'time' => $time);
@file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$txtFile = rtrim($dir, '/') . '/visits.txt';
$line = $ip . ' | ' . str_replace(array("\r", "\n"), ' ', $ua) . ' | ' . date('Y-m-d H:i:s') . "\n";
@file_put_contents($txtFile, $line, FILE_APPEND | LOCK_EX);

echo json_encode(array('ok' => true));
?>
