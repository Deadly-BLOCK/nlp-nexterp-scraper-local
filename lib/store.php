<?php
require_once __DIR__ . '/../config.php';

function user_key(string $admission, string $code): string {
    $norm = strtolower(trim($admission)) . "\0" . strtolower(trim($code));
    return substr(hash_hmac('sha256', $norm, USER_KEY_SECRET), 0, 16);
}
function valid_key(string $k): bool { return (bool) preg_match('/^[a-f0-9]{16}$/', $k); }

function require_key(): string {
    $k = isset($_GET['code']) ? (string) $_GET['code'] : '';
    if (!valid_key($k)) json_out(['error' => 'Invalid or missing code'], 400);
    return $k;
}

function user_dir(string $key): string {
    $dir = DATA_DIR . '/u-' . $key;
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    return $dir;
}

function read_json(string $path, $default) {
    if (!is_file($path)) return $default;
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') return $default;
    $d = json_decode($raw, true);
    return $d === null ? $default : $d;
}

function write_json(string $path, $data): bool {
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) return false;
    $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    if (!@rename($tmp, $path)) { @unlink($tmp); return false; }
    @chmod($path, 0600);
    return true;
}

function json_out($data, int $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data);
    exit;
}

function json_body(): array {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}

function enforce_method(array $allowed) {
    if (!in_array($_SERVER['REQUEST_METHOD'], $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        json_out(['error' => 'Method not allowed'], 405);
    }
}
