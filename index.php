<?php
require_once __DIR__ . '/config.php';
require_once LIB_DIR . '/store.php';

session_name(SESSION_NAME);
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$csrf  = $_SESSION['csrf'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, $token)) {
        $error = 'Session expired — please try again.';
    } else {
        $admission = trim($_POST['username'] ?? '');
        $password  = (string) ($_POST['password'] ?? '');
        $school    = trim($_POST['code'] ?? '');

        if ($admission === '' || $password === '' || $school === '') {
            $error = 'All fields are required.';
        } elseif (!preg_match('/^[A-Za-z0-9._@-]{1,64}$/', $admission) || !preg_match('/^[A-Za-z0-9._-]{1,64}$/', $school)) {
            $error = 'Admission number or school code has invalid characters.';
        } else {
            $key = user_key($admission, $school);

            $env = [
                'USERNAME'  => $admission,
                'PASSWORD'  => $password,
                'CODE'      => $school,
                'HASH_MODE' => 'md5',         
                'PATH'      => getenv('PATH') ?: '/usr/bin:/bin:/usr/local/bin',
            ];
            $cmd = escapeshellarg(NODE_BIN) . ' ' . escapeshellarg(SCRAPER_JS)
     . ' ' . escapeshellarg($key) . ' >/dev/null 2>&1 &';
            $proc = @proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, APP_ROOT, $env);
            if (is_resource($proc)) {
                foreach ($pipes as $p) { if (is_resource($p)) fclose($p); }
                proc_close($proc);
            }
            $password = null;

            header('Location: loading.php?code=' . urlencode($key), true, 303);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NextERP — Sign in</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--primary:#6366f1;--primary-glow:rgba(99,102,241,.4);--bg:#030712;--card-bg:rgba(17,24,39,.7);--text-main:#f3f4f6;--text-muted:#9ca3af;--err:#fca5a5;--err-bg:rgba(239,68,68,.12);--border:rgba(255,255,255,.08)}
*{box-sizing:border-box}
body{font-family:'Plus Jakarta Sans',sans-serif;background-color:var(--bg);background-image:radial-gradient(circle at 10% 20%,rgba(99,102,241,.1) 0,transparent 40%),radial-gradient(circle at 90% 80%,rgba(168,85,247,.08) 0,transparent 40%);color:var(--text-main);display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
.container{width:100%;max-width:420px;padding:24px}
.login-card{background:var(--card-bg);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-radius:28px;padding:40px;box-shadow:0 25px 50px -12px rgba(0,0,0,.8);border:1px solid var(--border);animation:cardEntrance .7s cubic-bezier(.2,.8,.2,1)}
@keyframes cardEntrance{from{opacity:0;transform:translateY(20px) scale(.98)}to{opacity:1;transform:none}}
.badge3p{display:inline-flex;align-items:center;gap:7px;font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#fcd34d;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);padding:6px 12px;border-radius:100px;margin-bottom:20px}
.badge3p .dot{width:5px;height:5px;border-radius:50%;background:#fcd34d}
.header{text-align:center;margin-bottom:28px}
.header h1{font-size:28px;font-weight:700;margin:0;letter-spacing:-.02em;background:linear-gradient(to right,#fff,#94a3b8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.header p{color:var(--text-muted);font-size:14px;margin:8px 0 0}
.input-group{margin-bottom:18px}
.input-group label{display:block;font-size:14px;font-weight:600;margin-bottom:8px;margin-left:4px;color:#d1d5db}
input{width:100%;padding:14px 18px;background:rgba(0,0,0,.2);border:1px solid var(--border);border-radius:14px;color:#fff;font-size:15px;transition:all .25s;font-family:inherit}
input:focus{outline:none;border-color:var(--primary);background:rgba(99,102,241,.05);box-shadow:0 0 0 4px var(--primary-glow)}
::placeholder{color:#4b5563}
.btn-login{width:100%;padding:16px;background:linear-gradient(135deg,#6366f1 0,#4f46e5 100%);color:#fff;border:none;border-radius:14px;font-weight:700;font-size:16px;cursor:pointer;transition:all .25s;margin-top:8px;box-shadow:0 10px 15px -3px rgba(99,102,241,.3);font-family:inherit}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 20px 25px -5px rgba(99,102,241,.4);filter:brightness(1.1)}
.error{background:var(--err-bg);color:var(--err);padding:12px 16px;border-radius:12px;font-size:14px;margin-bottom:20px;border:1px solid rgba(239,68,68,.25)}
.privacy{display:flex;gap:9px;align-items:flex-start;margin-top:22px;padding-top:18px;border-top:1px solid var(--border);font-size:12px;line-height:1.55;color:var(--text-muted)}
.privacy svg{flex:none;margin-top:1px;color:#a5b4fc}
.privacy b{color:#d1d5db;font-weight:600}
.foot{text-align:center;margin-top:18px;font-size:11.5px;color:#6b7280;line-height:1.5}
</style>
</head>
<body>
<div class="container">
<div class="login-card">
  <div class="header">
    <h1>NextERP Reader</h1>
    <p>Sign in to sync your school feed</p>
  </div>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?>
  <form method="POST" autocomplete="off">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
    <div class="input-group">
      <label>Admission Number</label>
      <input name="username" type="text" autocomplete="username" placeholder="8231" maxlength="64" required>
    </div>
    <div class="input-group">
      <label>Password</label>
      <input name="password" type="password" autocomplete="current-password" placeholder="••••••••" maxlength="200" required>
    </div>
    <div class="input-group">
      <label>School Code</label>
      <input name="code" type="text" placeholder="GDGPS" maxlength="64" required>
    </div>
    <button type="submit" class="btn-login">Sync now →</button>
  </form>
   </div>
  <div class="foot">Not affiliated with NextERP or your school. Use in line with your institution's policies.</div>
</div>
</div>
</body>
</html>
