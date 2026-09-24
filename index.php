<?php
/* index.php — the single entry point.
   PHP's job here: handle the fan-club form (validate → save → redirect),
   carry a flash message through the session, and render the whole page
   (cards, panels, characters, shows, episodes) from the data arrays. */

session_start();

$DATA = require __DIR__ . '/data.php';
require __DIR__ . '/includes/scenes.php';

function cu_e($s)
{
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/* ---------- POST handling: the fan-club form (Post/Redirect/Get) ---------- */

$flash = null;
$oldName = '';
$oldEmail = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'join') {
  $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 40);
  $email = trim((string) ($_POST['email'] ?? ''));

  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash'] = [
      'type' => 'error',
      'msg' => 'That email doesn’t look quite right — try again?',
      'old' => ['name' => $name, 'email' => $email]
    ];
  } else {
    // persist to data/subscribers.json (graceful if the disk says no)
    $file = __DIR__ . '/data/subscribers.json';
    @mkdir(dirname($file), 0777, true);
    $subs = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
    $subs[] = ['name' => $name !== '' ? $name : 'A friend', 'email' => $email, 'at' => date('c')];
    $count = count($subs);
    @file_put_contents($file, json_encode($subs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

    $who = $name !== '' ? $name : 'friend';
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => "Welcome to the club, {$who}! You’re friend #{$count}."];
  }
  header('Location: index.php?panel=about');
  exit;
}

if (!empty($_SESSION['flash'])) {
  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
  $oldName = $flash['old']['name'] ?? '';
  $oldEmail = $flash['old']['email'] ?? '';
}

$allowedPanels = ['home', 'characters', 'shows', 'episodes', 'games', 'about'];
$autopen = in_array($_GET['panel'] ?? '', $allowedPanels, true) ? $_GET['panel'] : null;

$fanFile = __DIR__ . '/data/subscribers.json';
$fanCount = is_file($fanFile) ? count(json_decode((string) file_get_contents($fanFile), true) ?: []) : 0;

$panelTitles = [
  'characters' => 'Meet the Crew',
  'shows' => 'Now Showing',
  'episodes' => 'Fresh Episodes',
  'games' => 'Arcade',
  'about' => 'About Us',
  'login' => './login.php'
];

$ICON_PLAY = '<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>';
$ICON_STAR = '<svg viewBox="0 0 24 24"><path d="M12 2.5l2.6 5.9 6.4.6-4.9 4.2 1.5 6.3L12 16.3 6.4 19.5l1.5-6.3L3 9l6.4-.6z" fill="currentColor"/></svg>';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= cu_e($DATA['site']['name']) ?> — A Hand-Drawn World</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Luckiest+Guy&display=swap"
    rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="intro-on">

  <!-- ============ CINEMATIC INTRO (cards rendered by PHP) ============ -->
  <div id="intro">
    <div id="introStage">
      <div id="introBg"></div>
      <div id="chainLayer">
        <?php foreach ($DATA['chain'] as $k): ?>
          <div class="card chain-card" data-scene="<?= cu_e($k) ?>">
            <div class="art"><?= cu_scene($k) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="whitePanel"></div>
      <div id="collageLayer">
        <div class="card collage-card main-card" data-scene="<?= cu_e($DATA['collage']['main']) ?>">
          <div class="art"><?= cu_scene($DATA['collage']['main']) ?></div>
        </div>
        <?php foreach ($DATA['collage']['sides'] as $k): ?>
          <div class="card collage-card<?= in_array($k, $DATA['collage']['land'], true) ? ' land' : '' ?>"
            data-scene="<?= cu_e($k) ?>">
            <div class="art"><?= cu_scene($k) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="brandMark">
        <span class="bm-badge"><?= $ICON_STAR ?></span>
        <span class="bm-text"><?= cu_e(strtoupper($DATA['site']['name'])) ?></span>
      </div>
      <div id="flash"></div>
    </div>
    <button id="skipBtn">SKIP INTRO
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
        stroke-linejoin="round">
        <path d="M5 12h13M13 6l6 6-6 6" />
      </svg>
    </button>
  </div>

  <!-- ============ MAIN SITE ============ -->
  <div id="site" aria-hidden="true">
    <div class="sky">
      <div class="sun-wrap">
        <div class="sun-halo"></div>
        <svg class="sun-svg" viewBox="0 0 120 120" aria-hidden="true">
          <g id="sunRays" stroke="#FFC531" stroke-width="9" stroke-linecap="round">
            <line x1="60" y1="10" x2="60" y2="26" />
            <line x1="60" y1="10" x2="60" y2="26" transform="rotate(45 60 60)" />
            <line x1="60" y1="10" x2="60" y2="26" transform="rotate(90 60 60)" />
            <line x1="60" y1="10" x2="60" y2="26" transform="rotate(135 60 60)" />
            <line x1="60" y1="10" x2="60" y2="26" transform="rotate(180 60 60)" />
            <line x1="60" y1="10" x2="60" y2="26" transform="rotate(225 60 60)" />
            <line x1="60" y1="10" x2="60" y2="26" transform="rotate(270 60 60)" />
            <line x1="60" y1="10" x2="60" y2="26" transform="rotate(315 60 60)" />
          </g>
          <circle cx="60" cy="60" r="30" fill="#FFC531" stroke="#33261D" stroke-width="4" />
          <path d="M44 56 q6 -6 12 0" stroke="#33261D" stroke-width="4" fill="none" stroke-linecap="round" />
          <path d="M64 56 q6 -6 12 0" stroke="#33261D" stroke-width="4" fill="none" stroke-linecap="round" />
          <path d="M53 68 q7 6 14 0" stroke="#33261D" stroke-width="4" fill="none" stroke-linecap="round" />
          <circle cx="40" cy="64" r="4.5" fill="#FF5A3C" opacity=".4" />
          <circle cx="80" cy="64" r="4.5" fill="#FF5A3C" opacity=".4" />
        </svg>
      </div>
      <div class="cloud c1"><?= cu_cloud() ?></div>
      <div class="cloud c2"><?= cu_cloud() ?></div>
      <div class="cloud c3"><?= cu_cloud() ?></div>
      <div class="cloud c4"><?= cu_cloud() ?></div>
    </div>

    <svg class="hills" viewBox="0 0 1440 320" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
      <path d="M0 150 Q170 70 360 130 Q520 178 700 122 Q900 64 1080 124 Q1260 180 1440 118 V320 H0 Z" fill="#CBE3B4" />
      <path d="M0 214 Q200 132 420 192 Q640 242 860 186 Q1090 136 1440 198 V320 H0 Z" fill="#8FCF96" />
      <g fill="#2F8A57">
        <circle cx="400" cy="196" r="26" />
        <rect x="394" y="208" width="12" height="26" fill="#8A5A38" />
        <circle cx="720" cy="190" r="30" />
        <rect x="713" y="204" width="14" height="30" fill="#8A5A38" />
        <circle cx="1060" cy="202" r="22" />
        <rect x="1054" y="212" width="11" height="22" fill="#8A5A38" />
      </g>
      <path d="M0 272 Q260 214 520 262 Q780 302 1040 256 Q1270 222 1440 266 V320 H0 Z" fill="#55B374" />
      <g fill="#3E9A63">
        <circle cx="300" cy="286" r="18" />
        <circle cx="880" cy="282" r="22" />
        <circle cx="1340" cy="280" r="16" />
      </g>
    </svg>

    <div class="floater f-fox" title="Ember"><span class="bob"><?= cu_floater('fox') ?></span></div>
    <div class="floater f-bunny" title="Pip"><span class="bob"><?= cu_floater('bunny') ?></span></div>
    <div class="floater f-star" title="Twinkle"><span class="bob"><?= cu_floater('star') ?></span></div>
    <div class="floater f-balloon"><span class="bob"><?= cu_floater('balloon') ?></span></div>
    <div id="particles"></div>

    <header class="site-head">
      <div class="brand">
        <span class="badge"><?= $ICON_STAR ?></span>
        <span><?= cu_e($DATA['site']['name']) ?></span>
      </div>
      <button id="replayBtn" class="icon-btn" title="Replay the intro" aria-label="Replay the intro">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
          stroke-linejoin="round">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
          <path d="M3 3v5h5" />
        </svg>
      </button>
    </header>

    <main class="hero">
      <h1 class="title-wrap"><span id="bigTitle">
          <span class="t-line">CARTOON</span>
          <span class="t-line">UNIVERSE</span>
        </span></h1>
      <p class="tagline"><?= cu_e($DATA['site']['tagline']) ?></p>
      <nav id="mainNav" aria-label="Main navigation">
        <button class="nav-btn active" data-panel="home">HOME</button>
        <button class="nav-btn" data-panel="characters">CHARACTERS</button>
        <button class="nav-btn" data-panel="shows">SHOWS</button>
        <button class="nav-btn" data-panel="episodes">EPISODES</button>
        <button class="nav-btn" data-panel="games">GAMES</button>
        <button class="nav-btn" data-panel="about">ABOUT</button>
        <button class="nav-btn" data-panel="login">LOGIN</button>
      </nav>
      <button class="ticket" data-panel="episodes">
        <span class="ticket-dot"></span>
        <span><?= cu_e($DATA['site']['episode_tag']) ?></span>
      </button>
    </main>
  </div>

  <div class="grain"></div>

  <!-- ============ OVERLAY (all five panels rendered by PHP) ============ -->
  <div id="overlay" hidden>
    <div class="scrim"></div>
    <section class="panel" role="dialog" aria-modal="true">
      <header>
        <h2 id="panelTitle"></h2>
        <button id="closePanel" class="icon-btn" aria-label="Close panel">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
            <path d="M6 6l12 12M18 6L6 18" />
          </svg>
        </button>
      </header>
      <div id="panelBody">

        <section class="panel-view" id="panel-characters" data-title="<?= cu_e($panelTitles['characters']) ?>" hidden>
          <div class="char-grid" data-stagger>
            <?php foreach ($DATA['characters'] as $i => $ch): ?>
              <article class="char-card<?= $i === 0 ? ' featured' : '' ?>">
                <div class="art"><?= cu_scene($ch['scene']) ?></div>
                <div>
                  <h3><?= cu_e($ch['name']) ?></h3>
                  <p class="role"><?= cu_e($ch['role']) ?></p>
                  <p><?= cu_e($ch['bio']) ?></p>
                  <?php foreach ($ch['stats'] ?? [] as $st): ?>
                    <div class="stat"><span><?= cu_e($st[0]) ?></span>
                      <span class="bar"><i style="width: <?= (int) $st[1] ?>%"></i></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="panel-view" id="panel-shows" data-title="<?= cu_e($panelTitles['shows']) ?>" hidden>
          <div data-stagger>
            <?php foreach ($DATA['shows'] as $s): ?>
              <div class="show-row">
                <div class="show-num"><?= cu_e($s['num']) ?></div>
                <div>
                  <h3 class="show-title"><?= cu_e($s['title']) ?></h3>
                  <p class="show-meta">
                    <span><?= (int) $s['seasons'] ?> seasons</span>
                    <span><?= (int) $s['episodes'] ?> episodes</span>
                    <span><?= cu_e($s['rating']) ?></span>
                  </p>
                  <p class="show-pitch"><?= cu_e($s['pitch']) ?></p>
                </div>
                <button class="btn-pill" data-watch="<?= cu_e($s['title']) ?>">Watch</button>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="panel-view" id="panel-episodes" data-title="<?= cu_e($panelTitles['episodes']) ?>" hidden>
          <p class="panel-note">Fresh from Meadowbrook — tap an episode to press play.</p>
          <div data-stagger>
            <?php foreach ($DATA['episodes'] as $ep): ?>
              <button class="ep-row" data-title="<?= cu_e($ep['title']) ?>">
                <span class="ep-play"><?= $ICON_PLAY ?></span>
                <span class="ep-info">
                  <span class="ep-tag">EP <?= (int) $ep['ep'] ?> · <?= (int) $ep['min'] ?> MIN</span>
                  <span class="ep-name"><?= cu_e($ep['title']) ?></span>
                </span>
                <?php if (!empty($ep['new'])): ?><span class="new-badge">NEW</span><?php endif; ?>
              </button>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="panel-view" id="panel-games" data-title="<?= cu_e($panelTitles['games']) ?>" hidden>
          <div id="gameIntro">
            <h3 class="g-title">STAR POP</h3>
            <p>Runaway stars are drifting out of Meadowbrook. Pop as many as you can in 20 seconds — golden ones are
              worth 3!</p>
            <div class="g-best">Personal best: <b id="gBest">0</b></div>
            <button class="btn-primary" id="gStart">START GAME</button>
          </div>
          <div id="gamePlay" hidden>
            <div class="game-hud"><span>SCORE <b id="gScore">0</b></span><span class="g-time" id="gTime">20s</span>
            </div>
            <div id="gameArena"></div>
            <p class="g-hint">Tap the stars before they float away.</p>
          </div>
        </section>

        <section class="panel-view" id="panel-about" data-title="<?= cu_e($panelTitles['about']) ?>" hidden>
          <div data-stagger>
            <p class="about-lead">Cartoon Universe is a very small studio with one very big rule: if it doesn’t make us
              laugh, it doesn’t ship.</p>
            <p>Every character, hill, and wobbly star on this page is drawn by hand in our little workshop above the
              Meadowbrook bakery — and served to you by a PHP engine that takes its coffee seriously.</p>
            <ul class="about-list">
              <?php foreach (['Story first, always.', 'Kind humor, no meanness.', 'Made for kids — and former kids.'] as $li): ?>
                <li><?= $ICON_STAR ?>   <?= cu_e($li) ?></li>
              <?php endforeach; ?>
            </ul>

            <form method="post" action="index.php" class="join-form">
              <input type="hidden" name="action" value="join">
              <label>YOUR NAME
                <input type="text" name="name" maxlength="40" placeholder="Ember" value="<?= cu_e($oldName) ?>">
              </label>
              <label>YOUR EMAIL
                <input type="email" name="email" required placeholder="you@meadowbrook.tv"
                  value="<?= cu_e($oldEmail) ?>">
              </label>
              <button class="btn-primary" type="submit">JOIN THE FAN CLUB</button>
            </form>
            <p class="join-note">
              <?php if ($fanCount > 0): ?>
                <?= (int) $fanCount ?> friend<?= $fanCount === 1 ? '' : 's' ?> in the club so far.
              <?php else: ?>
                Be the very first friend of the studio.
              <?php endif; ?>
            </p>
            <p class="about-sign">— The CU crew, somewhere in Meadowbrook</p>
          </div>
        </section>

      </div>
    </section>
  </div>

  <div id="toast" role="status"></div>
  <div id="svgAssets" hidden>
    <div id="asset-star"><?= cu_pop_star(false) ?></div>
    <div id="asset-star-gold"><?= cu_pop_star(true) ?></div>
  </div>

  <script>
    window.CU_BOOT = <?= json_encode(['autopen' => $autopen, 'flash' => $flash], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?: 'null' ?>;
  </script>
  <script src="assets/js/main.js"></script>
</body>

</html>