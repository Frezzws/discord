<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$dir = __DIR__ . '/../data';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$file = $dir . '/visits.json';

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$time = date('c');

$list = [];
if (file_exists($file)) {
    $list = json_decode(file_get_contents($file), true) ?: [];
}
$list[] = ['ip' => $ip, 'user_agent' => substr($ua, 0, 500), 'time' => $time];
file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true]);
?>
