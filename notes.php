<?php
require_once __DIR__ . '/config.php';
require_once LIB_DIR . '/store.php';

enforce_method(['GET', 'POST']);
$key  = require_key();
$file = user_dir($key) . '/notes.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_out(read_json($file, []));
}

$notes = json_body();
if (!is_array($notes)) json_out(['error' => 'Invalid payload'], 400);
if (count($notes) > NOTES_MAX) $notes = array_slice($notes, 0, NOTES_MAX);

$clean = [];
foreach ($notes as $n) {
    if (!is_array($n)) continue;
    $clean[] = [
        'id'      => (string) ($n['id'] ?? (string) (microtime(true) * 1000)),
        'text'    => mb_substr((string) ($n['text'] ?? ''), 0, 4000),
        'color'   => preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($n['color'] ?? '')) ? $n['color'] : '#7000ff',
        'done'    => (bool) ($n['done'] ?? false),
        'created' => (string) ($n['created'] ?? date('c')),
    ];
}
json_out(['ok' => write_json($file, $clean)]);
