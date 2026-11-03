<?php
/**
 * header.php — Shared global header/navigation for every page.
 *
 * Include exactly once per page, after setting $page, $pageTitle and $fullIntro.
 *
 *   $page = 'shows';
 *   $pageTitle = 'Shows — Cartoon Universe';
 *   $fullIntro = false;
 *   include __DIR__ . '/includes/header.php';
 *
 * footer.php is responsible for closing #site, </body> and </html>.
 */

require_once __DIR__ . '/config.php';
$DATA = require __DIR__ . '/data.php';
$INTRO_CONFIG = require __DIR__ . '/intro-config.php';

$bodyClass = $fullIntro ? 'intro-on' : '';
$siteAriaHidden = $fullIntro ? 'true' : 'false';

$isLoggedIn = (bool) $currentUser;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Luckiest+Guy&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= e($bodyClass) ?>">

<?php if ($fullIntro): ?>
  <!-- Cinematic intro — only on home page -->
  <div id="intro">
    <div id="introStage">
      <div id="introBg"></div>

      <!-- Phase 1: Chain Layer -->
      <div id="chainLayer">
        <?php foreach ($INTRO_CONFIG['chain'] as $index => $card): ?>
          <div class="card chain-card"
               data-index="<?= $index ?>"
               data-scene="<?= e($card['scene']) ?>"
               data-gif="<?= e($card['gif']) ?>"
               data-rotation="<?= $card['rotation'] ?>"
               data-enter-from="<?= e($card['enterFrom']) ?>">
            <div class="art">
              <img src="<?= e($card['gif']) ?>"
                   alt="<?= e($card['alt']) ?>"
                   style="width:100%;height:100%;object-fit:cover;"
                   onerror="this.onerror=null; this.src='data:image/svg+xml;base64,<?= base64_encode(cu_scene($card['scene'])) ?>'; this.style.opacity='1';" />
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Phase 2: White Panel -->
      <div id="whitePanel"></div>

      <!-- Phase 3/4: Collage Layer -->
      <div id="collageLayer">
        <!-- Main Card -->
        <div class="card collage-card main-card"
             data-scene="<?= e($INTRO_CONFIG['collage']['main']['scene']) ?>"
             data-gif="<?= e($INTRO_CONFIG['collage']['main']['gif']) ?>"
             data-landscape="<?= $INTRO_CONFIG['collage']['main']['landscape'] ? 'true' : 'false' ?>">
          <div class="art">
            <img src="<?= e($INTRO_CONFIG['collage']['main']['gif']) ?>"
                 alt="<?= e($INTRO_CONFIG['collage']['main']['alt']) ?>"
                 style="width:100%;height:100%;object-fit:cover;"
                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,<?= base64_encode(cu_scene($INTRO_CONFIG['collage']['main']['scene'], $INTRO_CONFIG['collage']['main']['landscape'])) ?>'; this.style.opacity='1';" />
          </div>
        </div>

        <!-- Side Cards -->
        <?php foreach ($INTRO_CONFIG['collage']['sides'] as $index => $card): ?>
          <div class="card collage-card<?= $card['landscape'] ? ' land' : '' ?>"
               data-scene="<?= e($card['scene']) ?>"
               data-position="<?= e($card['position']) ?>"
               data-gif="<?= e($card['gif']) ?>">
            <div class="art">
              <img src="<?= e($card['gif']) ?>"
                   alt="<?= e($card['alt']) ?>"
                   style="width:100%;height:100%;object-fit:cover;"
                   onerror="this.onerror=null; this.src='data:image/svg+xml;base64,<?= base64_encode(cu_scene($card['scene'], $card['landscape'])) ?>'; this.style.opacity='1';" />
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Brand Mark -->
      <div id="brandMark">
        <span class="bm-badge"><svg viewBox="0 0 24 24">
            <path d="M12 2.5l2.6 5.9 6.4 .6-4.9 4.2 1.5 6.3L12 16.3 6.4 19.5l1.5-6.3L3 9l6.4-.6z" fill="#FFFDF7" />
          </svg></span>
        <span class="bm-text"><?= e(strtoupper($DATA['site']['name'])) ?></span>
      </div>

      <!-- Flash -->
      <div id="flash"></div>
    </div>

    <!-- Skip Button -->
    <button id="skipBtn">SKIP INTRO
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h13M13 6l6 6-6 6" />
      </svg>
    </button>
  </div>
<?php endif; ?>

<div id="site" aria-hidden="<?= $siteAriaHidden ?>">

  <!-- ============================================
       ONE GLOBAL FIXED HEADER (rendered once)
       ============================================ -->
  <header class="site-head" id="siteHead">

    <!-- LEFT: Logo + Brand -->
    <a class="brand" href="index.php" aria-label="Cartoon Universe Home">
      <span class="badge" aria-hidden="true"><svg viewBox="0 0 24 24">
          <path d="M12 2.5l2.6 5.9 6.4 .6-4.9 4.2 1.5 6.3L12 16.3 6.4 19.5l1.5-6.3L3 9l6.4-.6z" fill="#FFFDF7" />
        </svg></span>
      <span><?= e($DATA['site']['name']) ?></span>
    </a>

    <!-- CENTER: Desktop navigation (one row; collapsed into sidebar below 1366px) -->
    <nav class="desktop-nav" aria-label="Main navigation">
      <?php foreach ($navItems as $item): ?>
        <a class="nav-btn<?= isActive($page, $item['page']) ?>" href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
      <?php endforeach; ?>
    </nav>

    <!-- RIGHT: Search, Auth, Hamburger controls -->
    <div class="nav-controls" aria-label="Header controls">

      <!-- Search (desktop) -->
      <div class="nav-search nav-search-desktop" role="search">
        <form action="search.php" method="GET" class="search-form" aria-label="Search cartoons, shows, characters">
          <input type="search" id="search-input" name="q" placeholder="Search cartoons, shows, characters..."
                 value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off" aria-label="Search">
          <button type="submit" class="search-btn" aria-label="Search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </button>
        </form>
      </div>

      <!-- Auth buttons (desktop) -->
      <div class="nav-auth nav-auth-desktop">
        <?php if ($isLoggedIn): ?>
          <a class="btn-pill" href="<?= e($authLinks['favorites'] ?? '#') ?>">Favorites</a>
          <a class="btn-pill" href="<?= e($authLinks['profile'] ?? '#') ?>">Profile</a>
          <a class="btn-primary" href="<?= e($authLinks['logout'] ?? 'logout.php') ?>">Log Out</a>
        <?php else: ?>
          <a class="btn-pill" href="<?= e($authLinks['login'] ?? 'login.php') ?>">Log In</a>
          <a class="btn-primary" href="<?= e($authLinks['signup'] ?? 'signup.php') ?>">Sign Up</a>
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

      <!-- Desktop hamburger — opens Categories Sidebar (desktop ≥1025px) -->
      <button class="desktop-menu-toggle" id="desktopMenuToggle" type="button" aria-label="Open categories menu" aria-expanded="false" aria-controls="desktopCategorySidebar">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>

      <!-- Mobile hamburger — opens Mobile Navigation Drawer (<1025px) -->
      <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobileNavigationDrawer">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>

    </div>
  </header>

  <!-- ============================================
       MOBILE NAVIGATION DRAWER (outside header flow)
       ============================================ -->
  <nav id="mobileNavigationDrawer" aria-label="Mobile navigation" hidden aria-hidden="true">
    <button class="mobile-drawer-close" id="mobileDrawerClose" type="button" aria-label="Close navigation menu">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>

    <ul class="mobile-drawer-nav">
      <?php foreach ($navItems as $item): ?>
        <li>
          <a class="nav-btn<?= isActive($page, $item['page']) ?>" href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
        </li>
      <?php endforeach; ?>
      <li>
        <a class="nav-btn" href="categories.php">Categories</a>
      </li>
    </ul>

    <!-- Search (inside mobile drawer) -->
    <div class="mobile-drawer-search" role="search">
      <form action="search.php" method="GET" class="search-form" aria-label="Search cartoons, shows, characters">
        <input type="search" id="search-input-mobile" name="q" placeholder="Search cartoons, shows, characters..."
               value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off" aria-label="Search">
        <button type="submit" class="search-btn" aria-label="Search">Search</button>
      </form>
    </div>

    <!-- Auth buttons (inside mobile drawer) -->
    <div class="nav-auth nav-auth-mobile">
      <?php if ($isLoggedIn): ?>
        <a class="btn-pill" href="<?= e($authLinks['favorites'] ?? '#') ?>">Favorites</a>
        <a class="btn-pill" href="<?= e($authLinks['profile'] ?? '#') ?>">Profile</a>
        <a class="btn-primary" href="<?= e($authLinks['logout'] ?? 'logout.php') ?>">Log Out</a>
      <?php else: ?>
        <a class="btn-pill" href="<?= e($authLinks['login'] ?? 'login.php') ?>">Log In</a>
        <a class="btn-primary" href="<?= e($authLinks['signup'] ?? 'signup.php') ?>">Sign Up</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ============================================
       DESKTOP CATEGORIES SIDEBAR (outside header flow)
       ============================================ -->
  <aside id="desktopCategorySidebar" aria-label="Categories sidebar" role="dialog" aria-modal="true" hidden aria-hidden="true">
    <div id="desktopSidebarOverlay" tabindex="-1"></div>
    <div class="desktop-sidebar-panel">
      <button class="desktop-sidebar-close" id="desktopSidebarClose" type="button" aria-label="Close categories menu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>

      <div class="desktop-sidebar-header">
        <h3>Categories &amp; Navigation</h3>
      </div>

      <nav class="desktop-sidebar-nav">
        <ul>
          <?php foreach ($navItems as $item): ?>
            <li>
              <a class="sidebar-nav-btn<?= isActive($page, $item['page']) ?>" href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <nav class="desktop-sidebar-nav">
        <h3>Categories</h3>
        <ul>
          <?php
          $genreLinks = [
            'Animation' => 'categories.php?genre=Animation',
            'Family' => 'categories.php?genre=Family',
            'Comedy' => 'categories.php?genre=Comedy',
            'Adventure' => 'categories.php?genre=Adventure',
            'Fantasy' => 'categories.php?genre=Fantasy',
            'Action' => 'categories.php?genre=Action',
            'Sci-Fi' => 'categories.php?genre=Sci-Fi',
            'Kids' => 'categories.php?genre=Kids',
            'Drama' => 'categories.php?genre=Drama',
            'Mystery' => 'categories.php?genre=Mystery',
            'Superhero' => 'categories.php?genre=Superhero',
          ];
          foreach ($genreLinks as $name => $href): ?>
            <li>
              <a class="sidebar-nav-btn" href="<?= e($href) ?>"><?= e($name) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <?php if ($isLoggedIn): ?>
        <nav class="desktop-sidebar-nav">
          <h3>Account</h3>
          <ul>
            <li><a class="sidebar-nav-btn" href="<?= e($authLinks['favorites'] ?? '#') ?>">Favorites</a></li>
            <li><a class="sidebar-nav-btn" href="<?= e($authLinks['profile'] ?? '#') ?>">Profile</a></li>
            <li><a class="sidebar-nav-btn" href="<?= e($authLinks['dashboard'] ?? 'dashboard.php') ?>">Dashboard</a></li>
            <li><a class="sidebar-nav-btn" href="<?= e($authLinks['logout'] ?? 'logout.php') ?>">Log Out</a></li>
          </ul>
        </nav>
      <?php else: ?>
        <nav class="desktop-sidebar-nav">
          <h3>Account</h3>
          <ul>
            <li><a class="sidebar-nav-btn" href="<?= e($authLinks['login'] ?? 'login.php') ?>">Log In</a></li>
            <li><a class="sidebar-nav-btn" href="<?= e($authLinks['signup'] ?? 'signup.php') ?>">Sign Up</a></li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </aside>

</div>
