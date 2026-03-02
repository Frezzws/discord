<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$dirs = array(__DIR__ . '/../data', __DIR__ . '/data');
$file = null;
foreach ($dirs as $d) {
    $f = rtrim($d, '/') . '/visits.json';
    if (file_exists($f) && is_readable($f)) { $file = $f; break; }
}
$list = array();
if ($file) {
    $raw = @file_get_contents($file);
    if ($raw) $list = json_decode($raw, true);
    if (!is_array($list)) $list = array();
}
echo json_encode(array_reverse($list));
?>
