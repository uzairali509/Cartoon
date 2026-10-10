<?php
/**
 * header.php — Shared header/navigation for all pages.
 * 
 * Include this at the top of each page after setting $page, $pageTitle, and $fullIntro.
 * 
 * Usage:
 *   $page = 'home';
 *   $pageTitle = 'Cartoon Universe — A Hand-Drawn World';
 *   $fullIntro = true;
 *   include 'includes/header.php';
 */

require_once __DIR__ . '/config.php';
$DATA = require __DIR__ . '/data.php';

// Determine body class for intro
$bodyClass = $fullIntro ? 'intro-on' : '';
$siteAriaHidden = $fullIntro ? 'true' : 'false';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Luckiest+Guy&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= $bodyClass ?>">

<?php if ($fullIntro): ?>
  <!-- Cinematic intro — only on home page -->
  <div id="intro">
    <div id="introStage">
      <div id="introBg"></div>
      <div id="chainLayer">
        <?php foreach ($DATA['chain'] as $scene): ?>
          <div class="card chain-card" data-scene="<?= htmlspecialchars($scene) ?>">
            <div class="art">
              <img src="assets/gifs/<?= htmlspecialchars($scene) ?>.gif" alt="<?= htmlspecialchars($scene) ?>" 
                   style="width:100%;height:100%;object-fit:cover;" 
                   onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjAwIDI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj44cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjI1MCIgZmlsbD0iI0ZGOWM2MyIvPjwvc3ZnPg=='; this.style.opacity='0.5';" />
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="whitePanel"></div>
      <div id="collageLayer">
        <div class="card collage-card main-card" data-scene="<?= htmlspecialchars($DATA['collage']['main']) ?>">
          <div class="art">
            <img src="assets/gifs/<?= htmlspecialchars($DATA['collage']['main']) ?>.gif" alt="<?= htmlspecialchars($DATA['collage']['main']) ?>"
                 style="width:100%;height:100%;object-fit:cover;"
                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjAwIDI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj44cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjI1MCIgZmlsbD0iI0ZGOWM2MyIvPjwvc3ZnPg=='; this.style.opacity='0.5';" />
          </div>
        </div>
        <?php foreach ($DATA['collage']['sides'] as $scene): ?>
          <div class="card collage-card<?= in_array($scene, $DATA['collage']['land']) ? ' land' : '' ?>" data-scene="<?= htmlspecialchars($scene) ?>">
            <div class="art">
              <img src="assets/gifs/<?= htmlspecialchars($scene) ?>.gif" alt="<?= htmlspecialchars($scene) ?>"
                   style="width:100%;height:100%;object-fit:cover;"
                   onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjAwIDI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj44cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjI1MCIgZmlsbD0iI0ZGOWM2MyIvPjwvc3ZnPg=='; this.style.opacity='0.5';" />
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="brandMark">
        <span class="bm-badge"><svg viewBox="0 0 24 24">
            <path d="M12 2.5l2.6 5.9 6.4.6-4.9 4.2 1.5 6.3L12 16.3 6.4 19.5l1.5-6.3L3 9l6.4-.6z" fill="#FFFDF7" />
          </svg></span>
        <span class="bm-text"><?= htmlspecialchars(strtoupper($DATA['site']['name'])) ?></span>
      </div>
      <div id="flash"></div>
    </div>
    <button id="skipBtn">SKIP INTRO
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h13M13 6l6 6-6 6" />
      </svg>
    </button>
  </div>
<?php endif; ?>

<div id="site" aria-hidden="<?= $siteAriaHidden ?>">

  <header class="site-head">
    <a class="brand" href="index.php" aria-label="Cartoon Universe Home">
      <span class="badge" aria-hidden="true"><svg viewBox="0 0 24 24">
          <path d="M12 2.5l2.6 5.9 6.4.6-4.9 4.2 1.5 6.3L12 16.3 6.4 19.5l1.5-6.3L3 9l6.4-.6z" fill="#FFFDF7" />
        </svg></span>
      <span><?= htmlspecialchars($DATA['site']['name']) ?></span>
    </a>
    
    <!-- Desktop Search Bar -->
    <div class="nav-search nav-search-desktop" role="search">
      <form action="search.php" method="GET" class="search-form" aria-label="Search cartoons, shows, characters">
        <input type="search" id="search-input" name="q" placeholder="Search cartoons, shows, characters..." 
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" autocomplete="off" aria-label="Search">
        <button type="submit" class="search-btn" aria-label="Search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </form>
      <div class="search-suggestions" id="searchSuggestions" hidden aria-live="polite"></div>
    </div>
    
    <!-- Desktop Auth Buttons -->
    <div class="nav-auth nav-auth-desktop">
      <?php if ($currentUser): ?>
        <a class="btn-pill" href="<?= $authLinks['favorites'] ?>">Favorites</a>
        <a class="btn-pill" href="<?= $authLinks['profile'] ?>">Profile</a>
        <a class="btn-primary" href="<?= $authLinks['logout'] ?>">Log Out</a>
      <?php else: ?>
        <a class="btn-pill" href="<?= $authLinks['login'] ?>">Log In</a>
        <a class="btn-primary" href="<?= $authLinks['signup'] ?>">Sign Up</a>
      <?php endif; ?>
      <?php if ($fullIntro): ?>
        <button id="replayBtn" class="icon-btn" title="Replay the intro" aria-label="Replay the intro">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
            <path d="M3 3v5h5" />
          </svg>
        </button>
      <?php endif; ?>
    </div>
    
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mainNav">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>
    
    <nav id="mainNav" aria-label="Main navigation">
      <?php foreach ($navItems as $item): ?>
        <a class="nav-btn<?= isActive($page, $item['page']) ?>" href="<?= $item['href'] ?>"><?= htmlspecialchars($item['label']) ?></a>
      <?php endforeach; ?>
      
      <!-- Search Bar (inside sidebar on mobile) -->
      <div class="nav-search nav-search-mobile" role="search" style="padding: 16px 0; border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle); margin: 16px 0;">
        <form action="search.php" method="GET" class="search-form" aria-label="Search cartoons, shows, characters">
          <input type="search" id="search-input-mobile" name="q" placeholder="Search cartoons, shows, characters..." 
                 value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" autocomplete="off" aria-label="Search" style="width: 100%; padding: 12px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-default); background: var(--bg-card); color: var(--text-primary);">
          <button type="submit" class="search-btn search-btn-mobile" aria-label="Search" style="margin-top: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <span style="margin-left: 8px;">Search</span>
          </button>
        </form>
        <div class="search-suggestions" id="searchSuggestionsMobile" hidden aria-live="polite"></div>
      </div>
      
      <!-- Auth buttons inside sidebar on mobile -->
      <div class="nav-auth nav-auth-mobile">
      <?php if ($currentUser): ?>
        <a class="btn-pill" href="<?= $authLinks['favorites'] ?>">Favorites</a>
        <a class="btn-pill" href="<?= $authLinks['profile'] ?>">Profile</a>
        <a class="btn-primary" href="<?= $authLinks['logout'] ?>">Log Out</a>
      <?php else: ?>
        <a class="btn-pill" href="<?= $authLinks['login'] ?>">Log In</a>
        <a class="btn-primary" href="<?= $authLinks['signup'] ?>">Sign Up</a>
      <?php endif; ?>
      <?php if ($fullIntro): ?>
        <button id="replayBtn" class="icon-btn" title="Replay the intro" aria-label="Replay the intro">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
            <path d="M3 3v5h5" />
          </svg>
        </button>
      <?php endif; ?>
    </div>
  </header>