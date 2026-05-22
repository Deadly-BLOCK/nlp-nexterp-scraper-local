<?php
require_once __DIR__ . '/config.php';
require_once LIB_DIR . '/store.php';

enforce_method(['GET', 'POST', 'DELETE']);
$key  = require_key();
$file = user_dir($key) . '/ai-history.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_out(read_json($file, []));
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    @unlink($file);
    json_out(['ok' => true]);
}

$turns = json_body();
if (!is_array($turns)) json_out(['error' => 'Invalid payload'], 400);
$turns = array_slice($turns, -AI_HISTORY_MAX);
$clean = [];
foreach ($turns as $t) {
    if (!is_array($t)) continue;
    $role = ($t['role'] ?? '') === 'user' ? 'user' : 'model';
    $clean[] = ['role' => $role, 'text' => mb_substr((string) ($t['text'] ?? ''), 0, 8000)];
}
json_out(['ok' => write_json($file, $clean)]);
