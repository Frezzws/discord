<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$file = file_exists(__DIR__ . '/../data/visits.json') ? __DIR__ . '/../data/visits.json' : __DIR__ . '/data/visits.json';
$list = array();
if (file_exists($file) && is_readable($file)) {
    $raw = @file_get_contents($file);
    if ($raw) $list = json_decode($raw, true);
    if (!is_array($list)) $list = array();
}
echo json_encode(array_reverse($list));
?>
