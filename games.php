<?php
/**
 * games.php — Games page with Star Pop game
 */

$page = 'games';
$pageTitle = 'Games — Cartoon Universe';
$fullIntro = false;

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title">Games</h1>
      <p class="page-sub">Play arcade games and test your skills</p>
    </header>

    <!-- Game List -->
    <div class="games-list">
      <article class="game-card featured" data-stagger>
        <div class="game-preview">
          <div class="game-poster">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 9 8.26 12 2"></polygon>
            </svg>
          </div>
        </div>
        <div class="game-info">
          <h3 class="game-title">STAR POP</h3>
          <p class="game-description">Runaway stars are drifting out of Meadowbrook. Pop as many as you can in 20 seconds — golden ones are worth 3!</p>
          <div class="game-meta">
            <span class="game-stat">High Score: <strong id="gBest">0</strong></span>
            <span class="game-stat">Time: <strong>20s</strong></span>
          </div>
          <button class="btn-primary" id="gStart">START GAME</button>
        </div>
      </article>

      <!-- Placeholder for future games -->
      <article class="game-card coming-soon" data-stagger>
        <div class="game-preview">
          <div class="game-poster">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.4;">
              <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
              <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4z"></path>
              <line x1="12" y1="16" x2="12" y2="16"></line>
            </svg>
          </div>
        </div>
        <div class="game-info">
          <h3 class="game-title">COMING SOON</h3>
          <p class="game-description">More arcade classics are in development. Stay tuned!</p>
          <button class="btn-pill" disabled>NOTIFY ME</button>
        </div>
      </article>

      <article class="game-card coming-soon" data-stagger>
        <div class="game-preview">
          <div class="game-poster">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.4;">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
              <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
          </div>
        </div>
        <div class="game-info">
          <h3 class="game-title">PUZZLE QUEST</h3>
          <p class="game-description">Solve animated puzzles with your favorite characters.</p>
          <button class="btn-pill" disabled>COMING SOON</button>
        </div>
      </article>
    </div>

    <!-- Star Pop Game (existing) -->
    <article class="game-card featured" id="starPopGame" data-stagger>
      <div class="game-preview">
        <div class="game-poster">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 9 8.26 12 2"></polygon>
          </svg>
        </div>
      </div>
      <div class="game-info">
        <h3 class="game-title">STAR POP</h3>
        <p class="game-description">Runaway stars are drifting out of Meadowbrook. Pop as many as you can in 20 seconds — golden ones are worth 3!</p>
        <div class="game-meta">
          <span class="game-stat">High Score: <strong id="gBest">0</strong></span>
          <span class="game-stat">Time: <strong>20s</strong></span>
        </div>
        <button class="btn-primary" id="gStart">START GAME</button>
      </div>
      <div id="gamePlay" hidden>
        <div class="game-hud"><span>SCORE <b id="gScore">0</b></span><span class="g-time" id="gTime">20s</span></div>
        <div id="gameArena"></div>
        <p class="g-hint">Tap the stars before they float away.</p>
      </div>
    </article>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>