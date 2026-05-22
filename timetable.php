<?php
require_once __DIR__ . '/config.php';
require_once LIB_DIR . '/store.php';

enforce_method(['GET', 'POST']);
$key  = require_key();
$file = user_dir($key) . '/timetable.json';

function default_timetable(): array {
    $cells = ['0,0' => ['v' => 'TIME', 'b' => true]];
    $days  = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
    foreach ($days as $i => $d) $cells['0,' . ($i + 1)] = ['v' => $d, 'b' => true, 'align' => 'center'];
    return [
        'rows'       => 9,
        'cols'       => 7,
        'cells'      => $cells,
        'colWidths'  => array_fill(0, 7, 120),
        'rowHeights' => array_fill(0, 9, 36),
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_out(read_json($file, default_timetable()));
}

$tt = json_body();
if (!isset($tt['cells']) || !is_array($tt['cells'])) json_out(['error' => 'Invalid timetable'], 400);
$tt['rows'] = max(1, min(60, (int) ($tt['rows'] ?? 9)));
$tt['cols'] = max(1, min(20, (int) ($tt['cols'] ?? 7)));
json_out(['ok' => write_json($file, $tt)]);