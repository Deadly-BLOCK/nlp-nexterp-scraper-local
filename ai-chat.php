<?php
require_once __DIR__ . '/config.php';
require_once LIB_DIR . '/store.php';

enforce_method(['POST']);

if (GEMINI_API_KEY === '') {
    json_out(['error' => 'AI is not configured. Set the GEMINI_API_KEY environment variable on the server.'], 503);
}

$body    = json_body();
$message = trim((string) ($body['message'] ?? ''));
$context = (string) ($body['context'] ?? '');
$history = is_array($body['history'] ?? null) ? $body['history'] : [];

if ($message === '') json_out(['error' => 'Empty message'], 400);

$system = "You are a helpful study assistant inside an unofficial NextERP feed reader. "
        . "Answer using ONLY the student's own feed and timetable context below. "
        . "Be concise and practical. If something isn't in the context, say so.\n\n"
        . "=== CONTEXT ===\n" . mb_substr($context, 0, 12000);

$contents = [];
$contents[] = ['role' => 'user', 'parts' => [['text' => $system]]];
$contents[] = ['role' => 'model', 'parts' => [['text' => 'Understood. I will help using that context.']]];

foreach (array_slice($history, -12) as $t) {
    if (!is_array($t)) continue;
    $role = ($t['role'] ?? '') === 'user' ? 'user' : 'model';
    $contents[] = ['role' => $role, 'parts' => [['text' => mb_substr((string) ($t['text'] ?? ''), 0, 4000)]]];
}
$contents[] = ['role' => 'user', 'parts' => [['text' => mb_substr($message, 0, 4000)]]];

$payload = json_encode([
    'contents'         => $contents,
    'generationConfig' => ['temperature' => 0.5, 'maxOutputTokens' => 800],
]);

$url = sprintf(
    'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
    rawurlencode(GEMINI_MODEL),
    rawurlencode(GEMINI_API_KEY)
);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => GEMINI_TIMEOUT,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($resp === false)          json_out(['error' => 'Upstream request failed: ' . $err], 502);
if ($code < 200 || $code >= 300) json_out(['error' => 'AI service returned HTTP ' . $code], 502);

$data  = json_decode($resp, true);
$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
if ($reply === null) json_out(['error' => 'No response from AI'], 502);

json_out(['reply' => $reply]);
