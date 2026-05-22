<?php
require_once __DIR__ . '/config.php';
require_once LIB_DIR . '/store.php';

enforce_method(['GET', 'POST', 'OPTIONS']);
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$key  = require_key();
$file = user_dir($key) . '/prefs.json';

$defaults = [
    'pins'             => [],
    'bookmarks'        => [],
    'readIds'          => [],
    'blocklist'        => ['teachers' => [], 'subjects' => []],
    'theme'            => 'dark',
    'accent'           => '#7000ff',
    'fontSize'         => 15,
    'soundAlerts'      => false,
    'sidebarCollapsed' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $saved = read_json($file, []);
    json_out(array_replace_recursive($defaults, is_array($saved) ? $saved : []));
}

$data = json_body();
if (isset($data['readIds']) && is_array($data['readIds']) && count($data['readIds']) > PREFS_MAX_READIDS) {
    $data['readIds'] = array_slice($data['readIds'], -PREFS_MAX_READIDS);
}
json_out(['ok' => write_json($file, $data)]);
