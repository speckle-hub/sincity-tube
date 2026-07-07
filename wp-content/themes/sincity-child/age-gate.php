<?php
/**
 * SinCity Age Gate — 18+ interstitial
 * HttpOnly cookie, 30-day expiry, underage redirect to Google.
 */
if (!defined('ABSPATH')) exit;
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Age Verification — SinCity</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; width: 100%; overflow: hidden; }
  body {
    background: #0a0a0f;
    color: #e0e0e0;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    display: flex; align-items: center; justify-content: center;
    min-height: 100vh;
    background-image:
      radial-gradient(ellipse at 20% 50%, rgba(255, 60, 60, 0.06) 0%, transparent 60%),
      radial-gradient(ellipse at 80% 50%, rgba(0, 255, 255, 0.06) 0%, transparent 60%);
  }
  .gate {
    max-width: 520px; width: 92%; padding: 2.5rem 2rem;
    background: linear-gradient(145deg, #111118 0%, #0d0d14 100%);
    border: 1px solid #1a1a28; border-radius: 20px;
    text-align: center; position: relative; overflow: hidden;
  }
  .gate::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, transparent, #ff3c3c, #00ffff, transparent);
  }
  .logo { font-family: 'Cinzel Decorative', serif; font-size: 1.8rem; font-weight: 700; letter-spacing: 6px; color: #ff3c3c; margin-bottom: 0.25rem; }
  .tagline { font-family: 'Cinzel', serif; font-size: 0.7rem; letter-spacing: 4px; text-transform: uppercase; color: #00ffff; opacity: 0.5; margin-bottom: 1.5rem; }
  h1 { font-family: 'Cinzel', serif; font-size: 1.1rem; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 0.75rem; color: #fff; }
  p { font-size: 0.85rem; line-height: 1.6; color: #999; margin-bottom: 1.5rem; }
  .btn-group { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 1.25rem; }
  .btn {
    padding: 0.75rem 2rem; border-radius: 8px; border: none;
    font-family: 'Inter', sans-serif; font-size: 0.85rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block;
  }
  .btn-primary { background: #ff3c3c; color: #fff; }
  .btn-primary:hover { background: #e63030; transform: translateY(-1px); box-shadow: 0 4px 20px rgba(255, 60, 60, 0.3); }
  .btn-secondary { background: transparent; color: #666; border: 1px solid #333; }
  .btn-secondary:hover { color: #ff3c3c; border-color: #ff3c3c; }
  .legal { font-size: 0.7rem; color: #555; line-height: 1.5; }
  .legal a { color: #00ffff; text-decoration: none; }
  .legal a:hover { text-decoration: underline; }
  .notice { font-size: 0.75rem; color: #ff3c3c; opacity: 0.6; margin-top: 1rem; }
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Cinzel:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="gate">
  <div class="logo">SINCITY</div>
  <div class="tagline">Adult Entertainment Hub</div>
  <h1>Are you over 18?</h1>
  <p>This website contains adult content of an explicit nature. You must be at least 18 years old to enter.</p>

  <form method="post" action="" class="btn-group">
    <button type="submit" name="sc_age_confirm" value="yes" class="btn btn-primary">I am 18+ — Enter</button>
    <button type="submit" name="sc_age_confirm" value="no" class="btn btn-secondary">I am under 18 — Leave</button>
  </form>

  <p class="legal">
    By entering, you agree to our
    <a href="/terms-of-service/">Terms of Service</a>,
    <a href="/privacy-policy/">Privacy Policy</a>, and
    <a href="/2257/">18 U.S.C. &sect;2257 Compliance</a>.
  </p>
  <div class="notice">This site uses cookies for age verification purposes.</div>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sc_age_confirm'])) {
    if ($_POST['sc_age_confirm'] === 'yes') {
        setcookie('sincity_age_verified', '1', time() + 2592000, '/', '', !empty($_SERVER['HTTPS']), true);
        $_SESSION['age_verified'] = true;
        header('Location: ' . esc_url(home_url(add_query_arg([], $_SERVER['REQUEST_URI']))));
        exit;
    } else {
        header('Location: https://www.google.com/');
        exit;
    }
}
?>
</body>
</html>
