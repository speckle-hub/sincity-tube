<?php
/**
 * SinCity — Age Verification Gate
 * Place in: sincity-child/age-gate.php
 *
 * Shows an 18+ interstitial before any site content.
 * Stores a 30-day secure cookie on verification.
 * Underage visitors are redirected away.
 */

// ─── Handle form submission ───────────────────────────────
$error = '';
if (isset($_POST['age_verify'])) {
    $day   = isset($_POST['birth_day'])   ? absint($_POST['birth_day']) : 0;
    $month = isset($_POST['birth_month']) ? absint($_POST['birth_month']) : 0;
    $year  = isset($_POST['birth_year'])  ? absint($_POST['birth_year']) : 0;

    // Validate date
    if (!checkdate($month, $day, $year)) {
        $error = 'Please enter a valid date of birth.';
    } else {
        $then = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
        $now  = new DateTime();
        $age  = $now->diff($then)->y;

        if ($age >= 18) {
            // Set secure cookie: HttpOnly + SameSite=Lax
            setcookie(
                'sincity_age_verified',
                '1',
                [
                    'expires'  => time() + 2592000, // 30 days
                    'path'     => '/',
                    'secure'   => !empty($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );
            $_SESSION['age_verified'] = true;
            wp_redirect(home_url());
            exit;
        } else {
            // Underage: redirect with a delay message
            $error = 'You are not old enough to access this site.';
            // Log the attempt (optional)
            // error_log('Underage attempt: IP ' . $_SERVER['REMOTE_ADDR']);
            ?>
            <!DOCTYPE html>
            <html><head>
                <title>Access Denied</title>
                <meta http-equiv="refresh" content="5;url=https://www.google.com">
                <style>
                    body { background:#0A0A0F; color:#FF1744; font-family:Inter,sans-serif;
                           display:flex; justify-content:center; align-items:center;
                           min-height:100vh; text-align:center; }
                    .msg { max-width:500px; padding:40px; }
                    h1 { font-size:2rem; margin-bottom:15px; }
                    p { color:#A0A0B8; }
                </style>
            </head><body>
                <div class="msg">
                    <h1>ACCESS DENIED</h1>
                    <p>You must be 18 or older to enter SinCity.</p>
                    <p>Redirecting to Google...</p>
                </div>
            </body></html>
            <?php exit;
        }
    }
}

// ─── Already verified? Bail out ────────────────────────────
$verified = !empty($_COOKIE['sincity_age_verified'])
    || (!empty($_SESSION['age_verified']) && $_SESSION['age_verified'] === true);
if ($verified) {
    return; // Continue to normal page rendering
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SinCity — Age Verification</title>
    <meta name="robots" content="noindex, nofollow, noarchive">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { height:100%; }
        body {
            background:#0A0A0F;
            color:#F0F0F5;
            font-family:'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
        }
        .gate {
            text-align:center;
            max-width:480px;
            width:90%;
            padding:40px 30px;
        }
        .gate h1 {
            font-family:'Cinzel Decorative', 'Cinzel', serif;
            font-size:3rem;
            color:#DC143C;
            margin-bottom:5px;
            letter-spacing:5px;
            text-shadow:0 0 40px rgba(220,20,60,0.3);
        }
        .gate .tagline {
            color:#606078;
            font-size:0.85rem;
            text-transform:uppercase;
            letter-spacing:4px;
            margin-bottom:30px;
        }
        .gate .warning {
            background:rgba(220,20,60,0.08);
            border:1px solid rgba(220,20,60,0.25);
            padding:18px 20px;
            border-radius:8px;
            font-size:0.85rem;
            color:#FF6B8A;
            margin-bottom:25px;
            line-height:1.7;
        }
        .gate .error {
            color:#FF1744;
            font-size:0.8rem;
            margin-bottom:15px;
        }
        .gate form { display:flex; flex-direction:column; gap:15px; }
        .gate .dob-row { display:flex; gap:10px; }
        .gate select {
            flex:1;
            padding:12px 10px;
            background:#1A1A28;
            border:1px solid #2A2A3E;
            color:#F0F0F5;
            border-radius:6px;
            font-size:0.95rem;
            font-family:inherit;
            cursor:pointer;
            appearance:none;
            -webkit-appearance:none;
            background-image:url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7'%3e%3cpath fill='%23606078' d='M1 .5l5 5 5-5'/%3e%3c/svg%3e");
            background-repeat:no-repeat;
            background-position:right 12px center;
        }
        .gate select:focus {
            outline:none;
            border-color:#DC143C;
        }
        .gate button {
            padding:14px;
            background:linear-gradient(135deg,#DC143C,#FF2D55);
            color:#fff;
            border:none;
            border-radius:6px;
            font-size:1rem;
            font-weight:600;
            font-family:inherit;
            cursor:pointer;
            text-transform:uppercase;
            letter-spacing:1.5px;
            transition:box-shadow 0.3s, transform 0.15s;
        }
        .gate button:hover {
            box-shadow:0 0 25px rgba(220,20,60,0.5);
        }
        .gate button:active {
            transform:scale(0.98);
        }
        .gate .links {
            margin-top:25px;
            font-size:0.75rem;
            color:#606078;
        }
        .gate .links a {
            color:#606078;
            text-decoration:underline;
        }
        .gate .links a:hover { color:#A0A0B8; }
        .gate .footer-note {
            margin-top:30px;
            font-size:0.7rem;
            color:#404058;
            line-height:1.6;
        }
        @media(max-width:480px) {
            .gate h1 { font-size:2.2rem; }
            .gate { padding:25px 15px; }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Cinzel:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="gate" role="dialog" aria-label="Age Verification">
        <h1>SINCITY</h1>
        <p class="tagline">Where Sin Meets Pleasure</p>

        <div class="warning" role="alert">
            <strong>&#9888; ADULT CONTENT</strong><br>
            This website contains sexually explicit material.<br>
            You must be <strong>18 years or older</strong> to enter.
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo esc_html($error); ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="dob-row">
                <select name="birth_day" required aria-label="Birth day">
                    <option value="">Day</option>
                    <?php for ($d = 1; $d <= 31; $d++): ?>
                        <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                    <?php endfor; ?>
                </select>
                <select name="birth_month" required aria-label="Birth month">
                    <option value="">Month</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                    <?php endfor; ?>
                </select>
                <select name="birth_year" required aria-label="Birth year">
                    <option value="">Year</option>
                    <?php for ($y = (int) date('Y'); $y >= 1940; $y--): ?>
                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" name="age_verify">I Am 18+ — Enter SinCity</button>
        </form>

        <div class="links">
            <a href="https://www.google.com" rel="nofollow noopener">I Am Under 18 — Leave</a><br>
            <a href="/privacy-policy/">Privacy Policy</a> &bull;
            <a href="/terms/">Terms of Service</a> &bull;
            <a href="/2257/">2257 Compliance</a>
        </div>

        <div class="footer-note">
            By entering, you agree to our Terms of Service and confirm<br>
            that you are legally allowed to view adult content in your country.
        </div>
    </div>
</body>
</html>
<?php exit; ?>
