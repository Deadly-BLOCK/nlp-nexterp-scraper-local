<?php
require_once __DIR__ . '/config.php';
require_once LIB_DIR . '/store.php';

$code = isset($_GET['code']) ? (string) $_GET['code'] : '';
if (!valid_key($code)) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>NextERP — Syncing</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#030014;--accent:#7000ff;--text:#fff;--muted:#94a3b8}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden}
body::before{content:"";position:absolute;width:100%;height:100%;background:radial-gradient(circle at 50% 50%,rgba(112,0,255,.08),transparent 70%);z-index:0}
.container{position:relative;z-index:10;text-align:center;width:90%;max-width:400px}
.brand{font-family:'Outfit',sans-serif;font-size:2.2rem;font-weight:800;letter-spacing:-.02em;background:linear-gradient(to bottom,#fff,#a5b4fc);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:40px;display:block}
.loader-box{position:relative;width:60px;height:60px;margin:0 auto 32px}
.dot-spinner{position:absolute;inset:0;border:3px solid rgba(112,0,255,.1);border-top-color:var(--accent);border-radius:50%;animation:spin 1s infinite cubic-bezier(.6,.2,.4,.8)}
@keyframes spin{to{transform:rotate(360deg)}}
h1{font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:700;margin:0 0 12px}
p{font-size:1rem;color:var(--muted);margin:0 0 30px;line-height:1.6}
.badge{display:inline-flex;align-items:center;padding:8px 20px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:100px;font-size:.85rem;color:#a5b4fc;font-weight:500}
.pulse{width:6px;height:6px;background:var(--accent);border-radius:50%;margin-right:10px;animation:breathe 2s infinite ease-in-out;box-shadow:0 0 10px var(--accent)}
@keyframes breathe{0%,100%{opacity:.4;transform:scale(.9)}50%{opacity:1;transform:scale(1.1)}}
.err{margin-top:22px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5;border-radius:12px;padding:12px 16px;font-size:.85rem;display:none}
.footer{margin-top:40px;font-size:.75rem;color:#4e4e6a;letter-spacing:.5px}
.bail{margin-top:18px;display:inline-block;color:#94a3b8;font-size:.8rem;text-decoration:none;border-bottom:1px dashed #475569}
.bail:hover{color:#fff}
</style>
</head>
<body>
<div class="container">
  <span class="brand">NextERP</span>
  <div class="loader-box"><div class="dot-spinner"></div></div>
  <h1>Syncing your feed…</h1>
  <p>We're logging in and pulling your latest posts.<br>This usually takes a minute or two.</p>
  <div class="badge"><div class="pulse"></div>Time elapsed: <span id="timer" style="margin-left:5px">0s</span></div>
  <div class="err" id="err"></div>
  <div class="footer">Please stay on this page</div>
  <a href="index.php" class="bail">Cancel</a>
</div>
<script>
const CODE = <?= json_encode($code) ?>;
const OK_URL  = 'posts/posts-' + CODE + '.json';
const ERR_URL = 'posts/posts-' + CODE + '.error.json';
let elapsed = 0;
const timerEl = document.getElementById('timer');
const errEl   = document.getElementById('err');
setInterval(() => { elapsed++; timerEl.textContent = elapsed + 's'; if (elapsed > 120) fail('Timed out. The sync worker took too long — try again.'); }, 1000);
function fail(msg){ errEl.style.display = 'block'; errEl.textContent = msg; }
async function poll(){
  // Did the worker report a login/sync error?
  try {
    const er = await fetch(ERR_URL + '?nc=' + Date.now(), { cache: 'no-store' });
    if (er.ok) { const j = await er.json(); return fail('Sync failed: ' + (j.error || 'unknown') + (/LOGIN/.test(j.error||'') ? ' — check your admission number, password and school code.' : '')); }
  } catch (_) {}
  // Is the feed ready?
  try {
    const r = await fetch(OK_URL + '?nc=' + Date.now(), { cache: 'no-store' });
    if (r.ok) return window.location.replace('feed.html?code=' + encodeURIComponent(CODE));
  } catch (_) {}
  if (elapsed <= 120) setTimeout(poll, 1000);
}
poll();
</script>
</body>
</html>
