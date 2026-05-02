<?php
// contactusaction.php — CSC331 Project · Michael Karaki & Rayen Estephanos
// Receives POST data from contactus.html and displays a styled thank-you page.

// ── Sanitize inputs ───────────────────────────────────────────
function clean($val) {
    return htmlspecialchars(strip_tags(trim($val ?? '')));
}

$firstname = clean($_POST['firstname'] ?? '');
$lastname  = clean($_POST['lastname']  ?? '');
$email     = clean($_POST['email']     ?? '');
$phone     = clean($_POST['phone']     ?? '');
$subject   = clean($_POST['subject']   ?? '');
$message   = clean($_POST['message']   ?? '');

// ── Basic server-side validation ─────────────────────────────
$errors = [];
if (empty($firstname))                 $errors[] = 'First name is required.';
if (empty($lastname))                  $errors[] = 'Last name is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
if (empty($subject))                   $errors[] = 'Subject is required.';
if (empty($message) || strlen($message) < 10) $errors[] = 'Message must be at least 10 characters.';

// Current timestamp
$time = date('F j, Y \a\t g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $errors ? 'Submission Error' : 'Message Received' ?> — M&R</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Instrument+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="style.css"/>
  <style>
    main{padding-top:var(--header-h);min-height:calc(100vh - var(--header-h));display:flex;align-items:flex-start;justify-content:center;padding-left:7vw;padding-right:7vw;}
    .action-wrap{max-width:680px;width:100%;padding:5rem 0 6rem;animation:fadeUp .6s ease both;}

    /* STATUS BADGE */
    .status-badge{display:inline-flex;align-items:center;gap:8px;border-radius:100px;padding:.4rem 1rem;font-family:'Instrument Sans',sans-serif;font-size:.75rem;font-weight:500;letter-spacing:.1em;text-transform:uppercase;margin-bottom:2rem;}
    .status-badge.success{border:1px solid rgba(74,222,128,.3);background:rgba(74,222,128,.07);color:#4ade80;}
    .status-badge.error{border:1px solid rgba(248,113,113,.3);background:rgba(248,113,113,.07);color:#f87171;}
    .status-dot{width:6px;height:6px;border-radius:50%;animation:pulse 2s infinite;}
    .status-badge.success .status-dot{background:#4ade80;}
    .status-badge.error .status-dot{background:#f87171;}

    .action-title{font-family:'Syne',sans-serif;font-size:clamp(2rem,5vw,3.5rem);font-weight:800;letter-spacing:-.03em;line-height:1;color:var(--c-white);margin-bottom:1rem;}
    .action-sub{font-family:'Instrument Sans',sans-serif;font-size:1rem;font-weight:300;color:var(--c-muted);line-height:1.8;margin-bottom:3rem;}

    /* DATA CARD */
    .data-card{background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--radius-xl);overflow:hidden;margin-bottom:2rem;}
    .data-card-header{padding:1.2rem 1.8rem;border-bottom:1px solid var(--c-border);display:flex;align-items:center;gap:.7rem;}
    .data-card-header-title{font-family:'Syne',sans-serif;font-size:.85rem;font-weight:700;color:var(--c-white);}
    .data-card-header-icon{width:32px;height:32px;border-radius:var(--radius-sm);background:rgba(0,255,163,.08);border:1px solid rgba(0,255,163,.18);display:flex;align-items:center;justify-content:center;color:var(--c-mint);font-size:.75rem;}
    .data-row{display:grid;grid-template-columns:150px 1fr;align-items:start;padding:1rem 1.8rem;border-bottom:1px solid var(--c-border);}
    .data-row:last-child{border-bottom:none;}
    .data-key{font-family:'Instrument Sans',sans-serif;font-size:.75rem;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:var(--c-subtle);padding-top:.1rem;}
    .data-val{font-family:'Instrument Sans',sans-serif;font-size:.92rem;color:var(--c-white);word-break:break-word;line-height:1.6;}
    .data-val.message-val{white-space:pre-wrap;color:var(--c-muted);}

    /* TIMESTAMP */
    .timestamp{display:flex;align-items:center;gap:6px;font-family:'Instrument Sans',sans-serif;font-size:.78rem;color:var(--c-subtle);margin-bottom:2rem;}
    .timestamp i{color:var(--c-mint);font-size:.72rem;}

    /* ERROR list */
    .error-list{background:rgba(248,113,113,.05);border:1px solid rgba(248,113,113,.2);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:2rem;}
    .error-list li{font-family:'Instrument Sans',sans-serif;font-size:.88rem;color:#f87171;padding:.35rem 0;display:flex;gap:.6rem;align-items:center;}
    .error-list li::before{content:'\f00d';font-family:'Font Awesome 6 Free';font-weight:900;font-size:.7rem;}

    /* ACTIONS */
    .action-buttons{display:flex;gap:.8rem;flex-wrap:wrap;}

    @media(max-width:600px){
      .data-row{grid-template-columns:1fr;}
      .data-key{margin-bottom:.3rem;}
      .action-wrap{padding:3rem 0 4rem;}
    }
  </style>
</head>
<body>
<header class="site-header">
  <a href="home.html" class="logo">M<span style="color:var(--c-mint)">&</span>R</a>
  <nav class="site-nav">
    <a href="home.html" class="nav-link">Home</a>
    <a href="aboutus.html" class="nav-link">About</a>
    <a href="schedules.html" class="nav-link">Schedule</a>
    <a href="research.html" class="nav-link">Research</a>
    <a href="media.html" class="nav-link">Media</a>
    <a href="quiz1.html" class="nav-link">Quiz</a>
    <a href="contactus.html" class="nav-link nav-cta"><i class="fa-regular fa-envelope"></i>&nbsp;Contact</a>
  </nav>
  <button class="nav-toggle" onclick="openMenu()"><span></span><span></span><span></span></button>
</header>
<div class="drawer-overlay" id="drawerOverlay" onclick="closeMenu()"></div>
<aside class="drawer" id="drawer">
  <div class="drawer-head">
    <span class="logo">M<span style="color:var(--c-mint)">&</span>R</span>
    <button class="drawer-close" onclick="closeMenu()"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <nav class="drawer-nav">
    <a href="home.html" class="drawer-link"><span class="d-num">01</span>Home</a>
    <a href="aboutus.html" class="drawer-link"><span class="d-num">02</span>About Us</a>
    <a href="schedules.html" class="drawer-link"><span class="d-num">03</span>Schedule</a>
    <a href="research.html" class="drawer-link"><span class="d-num">04</span>Research</a>
    <a href="media.html" class="drawer-link"><span class="d-num">05</span>Media</a>
    <a href="quiz1.html" class="drawer-link"><span class="d-num">06</span>Quiz 1</a>
    <a href="quiz2.php" class="drawer-link"><span class="d-num">07</span>Quiz 2</a>
    <a href="contactus.html" class="drawer-link active"><span class="d-num">08</span>Contact</a>
  </nav>
  <div class="drawer-footer">CSC331 · Web Programming</div>
</aside>

<main>
  <div class="action-wrap">

    <?php if (!empty($errors)): ?>
      <!-- ── ERROR STATE ─────────────────────────────────────── -->
      <div class="status-badge error"><div class="status-dot"></div> Submission failed</div>
      <h1 class="action-title">Oops,<br>something's off.</h1>
      <p class="action-sub">Your message couldn't be processed due to the following validation errors. Please go back and fix them.</p>
      <ul class="error-list">
        <?php foreach ($errors as $err): ?>
          <li><?= $err ?></li>
        <?php endforeach; ?>
      </ul>
      <div class="action-buttons">
        <a href="contactus.html" class="btn btn--mint"><i class="fa-solid fa-arrow-left"></i> Back to form</a>
      </div>

    <?php else: ?>
      <!-- ── SUCCESS STATE ───────────────────────────────────── -->
      <div class="status-badge success"><div class="status-dot"></div> Message received</div>
      <h1 class="action-title">Thank you,<br><?= $firstname ?>!</h1>
      <p class="action-sub">We've received your message and will get back to you at <strong style="color:var(--c-white)"><?= $email ?></strong> as soon as possible.</p>

      <div class="timestamp">
        <i class="fa-solid fa-clock"></i>
        Submitted on <?= $time ?>
      </div>

      <!-- SUBMITTED DATA DISPLAY -->
      <div class="data-card">
        <div class="data-card-header">
          <div class="data-card-header-icon"><i class="fa-solid fa-inbox"></i></div>
          <div class="data-card-header-title">Submitted Data</div>
        </div>
        <div class="data-row">
          <div class="data-key">Full Name</div>
          <div class="data-val"><?= $firstname . ' ' . $lastname ?></div>
        </div>
        <div class="data-row">
          <div class="data-key">Email</div>
          <div class="data-val"><a href="mailto:<?= $email ?>" style="color:var(--c-mint)"><?= $email ?></a></div>
        </div>
        <?php if (!empty($phone)): ?>
        <div class="data-row">
          <div class="data-key">Phone</div>
          <div class="data-val"><?= $phone ?></div>
        </div>
        <?php endif; ?>
        <div class="data-row">
          <div class="data-key">Subject</div>
          <div class="data-val"><?= $subject ?></div>
        </div>
        <div class="data-row">
          <div class="data-key">Message</div>
          <div class="data-val message-val"><?= $message ?></div>
        </div>
      </div>

      <div class="action-buttons">
        <a href="home.html" class="btn btn--mint"><i class="fa-solid fa-house"></i> Back to Home</a>
        <a href="contactus.html" class="btn btn--ghost"><i class="fa-solid fa-rotate-left"></i> Send another</a>
        <a href="research.html" class="btn btn--ghost"><i class="fa-solid fa-book-open"></i> Read Research</a>
      </div>
    <?php endif; ?>

  </div>
</main>

<footer class="site-footer">
  <div class="footer-inner">
    <span class="footer-brand">M<span style="color:var(--c-mint)">&</span>R</span>
    <p class="footer-copy">&copy; 2025 Michael Karaki &amp; Rayen Estephanos · CSC331</p>
    <div class="footer-links"><a href="aboutus.html">About</a><a href="contactus.html">Contact</a></div>
  </div>
</footer>
<script>
function openMenu(){document.getElementById('drawer').classList.add('open');document.getElementById('drawerOverlay').classList.add('visible');document.body.style.overflow='hidden';}
function closeMenu(){document.getElementById('drawer').classList.remove('open');document.getElementById('drawerOverlay').classList.remove('visible');document.body.style.overflow='';}
</script>
</body>
</html>
