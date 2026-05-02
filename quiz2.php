<?php
// quiz2.php — CSC331 Project · Michael Karaki & Rayen Estephanos
// Server-side quiz: form displays questions, PHP grades answers and returns results.

// ── Answer key (server-side, never exposed to client) ────────
$answer_key = [
    1 => 'a',   // Internet Protocol
    2 => 'c',   // Tim Berners-Lee
    3 => 'c',   // HTTPS
    4 => 'hypertext markup language', // text
    5 => 'b',   // Domain Name System
    6 => 'd',   // PHP
    7 => 'c',   // ARPANET
    8 => '80',  // HTTP port
    9 => 'c',   // display: flex
    10 => 'a',  // SQL
];

$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$results   = [];
$score     = 0;

if ($submitted) {
    foreach ($answer_key as $qn => $correct) {
        $user = strtolower(trim($_POST["q{$qn}"] ?? ''));
        $is_correct = ($user === strtolower($correct));
        // For text questions allow partial match
        if (in_array($qn, [4, 8]) && !$is_correct && !empty($user)) {
            $is_correct = str_contains(strtolower($correct), $user) && strlen($user) > 2;
        }
        if ($is_correct) $score++;
        $results[$qn] = [
            'user'    => htmlspecialchars($_POST["q{$qn}"] ?? '(blank)'),
            'correct' => $is_correct,
            'answer'  => $correct,
        ];
    }
}

$pct   = $submitted ? round($score / count($answer_key) * 100) : 0;
$grade = $pct >= 90 ? 'Excellent' : ($pct >= 70 ? 'Great work' : ($pct >= 50 ? 'Good effort' : 'Keep studying'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Quiz 2 (PHP) — M&R</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Instrument+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="style.css"/>
  <style>
    main{padding-top:var(--header-h);}
    .quiz-wrap{max-width:760px;margin:0 auto;padding:4rem 5vw 6rem;}

    /* HERO */
    .quiz-hero{text-align:center;margin-bottom:3.5rem;animation:fadeUp .6s ease both;}
    .quiz-hero .badge{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(99,102,241,.3);background:rgba(99,102,241,.07);color:#a5b4fc;font-family:'Instrument Sans',sans-serif;font-size:.73rem;font-weight:500;letter-spacing:.12em;text-transform:uppercase;padding:.38rem 1rem;border-radius:100px;margin-bottom:1.5rem;}
    .quiz-hero h1{font-family:'Syne',sans-serif;font-size:clamp(2rem,5vw,3.2rem);font-weight:800;letter-spacing:-.03em;color:var(--c-white);margin-bottom:.8rem;}
    .quiz-hero p{font-family:'Instrument Sans',sans-serif;font-size:.97rem;font-weight:300;color:var(--c-muted);line-height:1.8;}

    /* PHP BADGE */
    .php-note{display:flex;align-items:center;gap:.7rem;background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.2);border-radius:var(--radius-md);padding:.9rem 1.2rem;margin-bottom:2.5rem;font-family:'Instrument Sans',sans-serif;font-size:.83rem;color:#a5b4fc;}
    .php-note i{color:#a5b4fc;font-size:.85rem;}

    /* QUESTION CARD */
    .q-card{background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--radius-xl);padding:2rem 2.2rem;margin-bottom:1.2rem;transition:border-color .3s;}
    .q-card:hover{border-color:rgba(255,255,255,.12);}

    /* Result state */
    .q-card.q-correct{border-color:rgba(74,222,128,.3);background:rgba(74,222,128,.04);}
    .q-card.q-wrong{border-color:rgba(248,113,113,.3);background:rgba(248,113,113,.04);}

    .q-top{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1rem;}
    .q-num{font-family:'Syne',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--c-subtle);}
    .q-cat{font-family:'Instrument Sans',sans-serif;font-size:.7rem;letter-spacing:.08em;text-transform:uppercase;color:var(--c-mint);background:rgba(0,255,163,.07);border:1px solid rgba(0,255,163,.15);padding:.2rem .65rem;border-radius:100px;}
    .q-text{font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;color:var(--c-white);margin-bottom:1.3rem;line-height:1.4;}

    /* MCQ OPTIONS */
    .mcq-options{display:flex;flex-direction:column;gap:.55rem;}
    .mcq-label{display:flex;align-items:center;gap:.9rem;padding:.8rem 1rem;border-radius:var(--radius-md);border:1px solid var(--c-border);cursor:pointer;transition:all .2s;position:relative;}
    .mcq-label:hover{border-color:rgba(99,102,241,.4);background:rgba(99,102,241,.05);}
    .mcq-label input[type="radio"]{position:absolute;opacity:0;width:0;height:0;}
    .mcq-label input[type="radio"]:checked ~ .opt-key{background:#6366f1;border-color:#6366f1;color:#fff;}
    .mcq-label input[type="radio"]:checked ~ .opt-txt{color:var(--c-white);}
    .opt-key{width:30px;height:30px;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid var(--c-border);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-size:.72rem;font-weight:700;color:var(--c-muted);flex-shrink:0;transition:all .2s;}
    .opt-txt{font-family:'Instrument Sans',sans-serif;font-size:.9rem;color:var(--c-muted);}

    /* RESULT OPTION STATES */
    .opt-correct .mcq-label{border-color:rgba(74,222,128,.4);background:rgba(74,222,128,.06);}
    .opt-correct .opt-key{background:#4ade80;border-color:#4ade80;color:#021a08;}
    .opt-correct .opt-txt{color:#4ade80;}
    .opt-user-wrong .mcq-label{border-color:rgba(248,113,113,.4);background:rgba(248,113,113,.06);}
    .opt-user-wrong .opt-key{background:#f87171;border-color:#f87171;color:#2a0000;}
    .opt-user-wrong .opt-txt{color:#f87171;}

    /* TEXT INPUT */
    .text-input{width:100%;background:var(--c-surface2);border:1px solid var(--c-border2);border-radius:var(--radius-md);padding:.82rem 1.1rem;font-family:'Instrument Sans',sans-serif;font-size:.92rem;color:var(--c-white);outline:none;transition:border-color .2s;}
    .text-input:focus{border-color:rgba(99,102,241,.5);box-shadow:0 0 0 3px rgba(99,102,241,.08);}
    .text-input.result-correct{border-color:#4ade80;background:rgba(74,222,128,.06);color:#4ade80;}
    .text-input.result-wrong{border-color:#f87171;background:rgba(248,113,113,.06);}

    /* Correct answer reveal */
    .correct-reveal{margin-top:.7rem;font-family:'Instrument Sans',sans-serif;font-size:.8rem;color:var(--c-muted);display:flex;align-items:center;gap:5px;}
    .correct-reveal strong{color:#4ade80;}
    .result-icon{font-size:.9rem;margin-left:auto;}

    /* SUBMIT */
    .quiz-submit{display:flex;justify-content:center;margin-top:2rem;}

    /* RESULTS PANEL */
    .results-panel{background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--radius-xl);padding:3rem;text-align:center;margin-bottom:2.5rem;animation:fadeUp .5s ease both;}
    .results-panel .big-score{font-family:'Syne',sans-serif;font-size:5rem;font-weight:800;line-height:1;margin-bottom:.5rem;}
    .results-panel .big-score.excellent{color:var(--c-mint);}
    .results-panel .big-score.great{color:#a5b4fc;}
    .results-panel .big-score.good{color:#fbbf24;}
    .results-panel .big-score.poor{color:#f87171;}
    .results-panel .grade-label{font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;color:var(--c-white);margin-bottom:.5rem;}
    .results-panel .grade-sub{font-family:'Instrument Sans',sans-serif;font-size:.9rem;color:var(--c-muted);}
    .results-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-top:2rem;}
    .rstat{background:var(--c-surface2);border:1px solid var(--c-border);border-radius:var(--radius-md);padding:1.1rem;}
    .rstat-n{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;}
    .rstat-l{font-family:'Instrument Sans',sans-serif;font-size:.72rem;color:var(--c-muted);margin-top:.2rem;}
    .rstat.c .rstat-n{color:#4ade80;}
    .rstat.w .rstat-n{color:#f87171;}
    .rstat.p .rstat-n{color:var(--c-mint);}

    /* PHP SERVER BADGE on results */
    .server-badge{display:inline-flex;align-items:center;gap:6px;font-family:'Instrument Sans',sans-serif;font-size:.72rem;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:#a5b4fc;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:100px;padding:.3rem .85rem;margin-bottom:1.5rem;}

    @media(max-width:600px){.results-stats{grid-template-columns:1fr 1fr;}}
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
    <a href="quiz1.html" class="nav-link active">Quiz</a>
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
    <a href="quiz2.php" class="drawer-link active"><span class="d-num">07</span>Quiz 2</a>
    <a href="contactus.html" class="drawer-link"><span class="d-num">08</span>Contact</a>
  </nav>
  <div class="drawer-footer">CSC331 · Web Programming</div>
</aside>

<main>
<div class="quiz-wrap">

  <!-- HERO -->
  <div class="quiz-hero">
    <div class="badge"><i class="fa-solid fa-server"></i> Server-side · PHP scored</div>
    <h1>Quiz 2 — PHP Edition</h1>
    <p>Same 10 questions, but this time your answers are submitted to the server and graded entirely by PHP. The answer key never touches your browser.</p>
  </div>

  <?php if ($submitted): ?>
  <!-- ─── RESULTS VIEW ─────────────────────────────────────── -->
  <div class="results-panel">
    <div class="server-badge"><i class="fa-solid fa-server"></i> Scored by PHP on the server</div>
    <?php
      $cls = $pct>=90?'excellent':($pct>=70?'great':($pct>=50?'good':'poor'));
    ?>
    <div class="big-score <?= $cls ?>"><?= $pct ?>%</div>
    <div class="grade-label"><?= $grade ?>!</div>
    <div class="grade-sub"><?= $score ?> correct out of <?= count($answer_key) ?> questions</div>
    <div class="results-stats">
      <div class="rstat c"><div class="rstat-n"><?= $score ?></div><div class="rstat-l">Correct</div></div>
      <div class="rstat w"><div class="rstat-n"><?= count($answer_key)-$score ?></div><div class="rstat-l">Wrong</div></div>
      <div class="rstat p"><div class="rstat-n"><?= $pct ?>%</div><div class="rstat-l">Score</div></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ─── QUESTION FORM ────────────────────────────────────── -->
  <?php if (!$submitted): ?>
  <div class="php-note">
    <i class="fa-solid fa-lock"></i>
    The answer key lives only on the server. Scoring happens in PHP — not in your browser's JavaScript.
  </div>
  <?php endif; ?>

  <?php
  $questions = [
    1  => ['cat'=>'Internet Basics','text'=>'What does "IP" stand for in IP address?','type'=>'mcq','opts'=>['a'=>'Internet Protocol','b'=>'Internal Port','c'=>'Input Parameter','d'=>'Interface Proxy']],
    2  => ['cat'=>'Web History','text'=>'Who invented the World Wide Web in 1989?','type'=>'mcq','opts'=>['a'=>'Bill Gates','b'=>'Vint Cerf','c'=>'Tim Berners-Lee','d'=>'Steve Jobs']],
    3  => ['cat'=>'Protocols','text'=>'Which protocol is used to securely transfer web pages?','type'=>'mcq','opts'=>['a'=>'FTP','b'=>'SMTP','c'=>'HTTPS','d'=>'SSH']],
    4  => ['cat'=>'Web Tech','text'=>'What does HTML stand for? (full form, lowercase)','type'=>'text'],
    5  => ['cat'=>'Networks','text'=>'What does DNS stand for?','type'=>'mcq','opts'=>['a'=>'Dynamic Network System','b'=>'Domain Name System','c'=>'Digital Node Service','d'=>'Data Naming Schema']],
    6  => ['cat'=>'Web Tech','text'=>'Which of the following is a server-side scripting language?','type'=>'mcq','opts'=>['a'=>'HTML','b'=>'CSS','c'=>'JavaScript','d'=>'PHP']],
    7  => ['cat'=>'Internet','text'=>'What was the name of the first network that became the Internet?','type'=>'mcq','opts'=>['a'=>'NSFNET','b'=>'MILNET','c'=>'ARPANET','d'=>'BITNET']],
    8  => ['cat'=>'Protocols','text'=>'What port number does HTTP use by default?','type'=>'text'],
    9  => ['cat'=>'Web Dev','text'=>'Which CSS property creates a flexible, responsive layout?','type'=>'mcq','opts'=>['a'=>'display: block','b'=>'position: relative','c'=>'display: flex','d'=>'float: left']],
    10 => ['cat'=>'Databases','text'=>'What does SQL stand for?','type'=>'mcq','opts'=>['a'=>'Structured Query Language','b'=>'Simple Queue Language','c'=>'System Query Logic','d'=>'Standard Question List']],
  ];
  ?>

  <form method="POST" action="quiz2.php">
    <?php foreach($questions as $n => $q): ?>
      <?php
        $card_class = '';
        $result_data = $results[$n] ?? null;
        if($submitted && $result_data) $card_class = $result_data['correct'] ? 'q-correct' : 'q-wrong';
      ?>
      <div class="q-card <?= $card_class ?>">
        <div class="q-top">
          <div class="q-num">Question <?= $n ?> of <?= count($questions) ?></div>
          <div class="q-cat"><?= $q['cat'] ?></div>
        </div>
        <div class="q-text"><?= $q['text'] ?></div>

        <?php if($q['type']==='mcq'): ?>
          <div class="mcq-options">
          <?php foreach($q['opts'] as $key => $opt):
            $is_correct_opt = ($key === strtolower($answer_key[$n]));
            $is_user_opt    = $submitted && strtolower($results[$n]['user']) === $opt || strtolower($results[$n]['user'] ?? '') === $key;
            $user_was_wrong_here = $submitted && !$results[$n]['correct'] && strtolower($results[$n]['user']) === $opt;

            $wrap_class = '';
            if($submitted){
              if($is_correct_opt) $wrap_class = 'opt-correct';
              elseif($user_was_wrong_here) $wrap_class = 'opt-user-wrong';
            }
          ?>
            <div class="<?= $wrap_class ?>">
              <label class="mcq-label">
                <input type="radio" name="q<?= $n ?>" value="<?= $opt ?>" <?= $submitted?'disabled':'' ?> <?= ($submitted && strtolower($results[$n]['user']??'')===strtolower($opt))?'checked':'' ?>>
                <div class="opt-key"><?= strtoupper($key) ?></div>
                <div class="opt-txt"><?= htmlspecialchars($opt) ?></div>
                <?php if($submitted): ?>
                  <?php if($is_correct_opt): ?><i class="fa-solid fa-check result-icon" style="margin-left:auto;color:#4ade80"></i>
                  <?php elseif($user_was_wrong_here): ?><i class="fa-solid fa-xmark result-icon" style="margin-left:auto;color:#f87171"></i><?php endif; ?>
                <?php endif; ?>
              </label>
            </div>
          <?php endforeach; ?>
          </div>

        <?php else: // text ?>
          <?php
            $tc = '';
            if($submitted) $tc = $results[$n]['correct'] ? 'result-correct' : 'result-wrong';
          ?>
          <input type="text" name="q<?= $n ?>" class="text-input <?= $tc ?>"
            placeholder="Type your answer…"
            value="<?= htmlspecialchars($results[$n]['user'] ?? '') ?>"
            <?= $submitted?'disabled':'' ?>/>
          <?php if($submitted && !$results[$n]['correct']): ?>
            <div class="correct-reveal"><i class="fa-solid fa-circle-check" style="color:#4ade80"></i> Correct answer: <strong><?= htmlspecialchars($answer_key[$n]) ?></strong></div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <?php if(!$submitted): ?>
      <div class="quiz-submit">
        <button type="submit" class="btn btn--mint" style="font-size:1rem;padding:.9rem 2.5rem">
          <i class="fa-solid fa-server"></i> Submit to PHP Server
        </button>
      </div>
    <?php else: ?>
      <div class="quiz-submit" style="gap:.8rem;flex-wrap:wrap">
        <a href="quiz2.php" class="btn btn--mint"><i class="fa-solid fa-rotate-right"></i> Retake Quiz</a>
        <a href="quiz1.html" class="btn btn--ghost"><i class="fa-solid fa-code"></i> Try Quiz 1 (JS)</a>
      </div>
    <?php endif; ?>
  </form>

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
