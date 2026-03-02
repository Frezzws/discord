<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$file = __DIR__ . '/../data/visits.json';
$list = [];
if (file_exists($file)) {
    $list = json_decode(file_get_contents($file), true) ?: [];
}
echo json_encode(array_reverse($list));
?>
