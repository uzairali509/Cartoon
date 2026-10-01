<?php
/**
 * player.php — Video player page
 */

$page = 'player';
$pageTitle = 'Watch — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

// Require login for player
requireLogin();

$showId = (int)($_GET['show_id'] ?? 0);
$seasonNumber = (int)($_GET['season'] ?? 1);
$episodeNumber = (int)($_GET['episode'] ?? 1);

if (!$showId || !$seasonNumber || !$episodeNumber) {
    header('Location: shows.php');
    exit;
}

// Fetch show and episode details
$show = $tmdb->getTvDetails($showId);
if (!$show) {
    header('Location: shows.php');
    exit;
}

$showData = $tmdb->formatTvShow($show);
$episode = $tmdb->getEpisodeDetails($showId, $seasonNumber, $episodeNumber);
if (!$episode) {
    header("Location: season.php?show_id=$showId&season=$seasonNumber");
    exit;
}

$episodeData = $tmdb->formatEpisode($episode);

// Find prev/next episodes
$seasonData = $tmdb->getSeasonEpisodes($showId, $seasonNumber);
$allEpisodes = [];
if ($seasonData && !empty($seasonData['episodes'])) {
    foreach ($seasonData['episodes'] as $ep) {
        $allEpisodes[] = $tmdb->formatEpisode($ep);
    }
}

$currentIndex = array_search($episodeNumber, array_column($allEpisodes, 'episode_number'));
$prevEpisode = $currentIndex > 0 ? $allEpisodes[$currentIndex - 1] : null;
$nextEpisode = $currentIndex !== false && $currentIndex < count($allEpisodes) - 1 ? $allEpisodes[$currentIndex + 1] : null;

$pageTitle = $showData['title'] . ' — S' . str_pad($seasonNumber, 2, '0', STR_PAD_LEFT) . 'E' . str_pad($episodeNumber, 2, '0', STR_PAD_LEFT) . ' — Watch';

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap player-page">
  <section class="player-section">
    <!-- Video Player -->
    <div class="video-player-wrapper">
      <div class="video-player-container" id="videoPlayerContainer">
        <video 
          id="videoPlayer" 
          class="video-player" 
          controls 
          crossorigin="anonymous"
          playsinline
          poster="<?= !empty($episodeData['still_url']) ? htmlspecialchars($episodeData['still_url']) : '' ?>"
        >
          <source src="" type="video/mp4">
          <track kind="captions" src="" srclang="en" label="English" default>
          Your browser does not support the video tag.
        </video>
        
        <div class="player-unavailable" id="playerUnavailable" style="display: none;">
          <div class="player-message">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px; opacity: 0.5;">
              <polygon points="23 7 16 12 23 17 23 7"></polygon>
              <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
            </svg>
            <h3>Video playback is currently unavailable.</h3>
            <p>We don't have a licensed video source for this episode.</p>
            <p class="player-note">This is a demo platform. In a production environment, you would integrate with licensed streaming providers.</p>
          </div>
        </div>
      </div>
      
      <div class="player-controls-overlay" id="playerControls">
        <div class="progress-bar">
          <div class="progress-fill" id="progressFill"></div>
          <div class="progress-handle" id="progressHandle"></div>
        </div>
        <div class="control-bar">
          <div class="control-left">
            <button class="control-btn" id="rewindBtn" title="Rewind 10s" aria-label="Rewind 10 seconds">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 19 5 12 11 5"></polygon><path d="M21 12a9 9 0 1 1-6.48-7.55"></path></svg>
            </button>
            <button class="control-btn play-pause-btn" id="playPauseBtn" aria-label="Play/Pause">
              <svg class="play-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
              <svg class="pause-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
            </button>
            <button class="control-btn" id="forwardBtn" title="Forward 10s" aria-label="Forward 10 seconds">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 19 19 12 13 5"></polygon><path d="M3 12a9 9 0 1 0 6.48-7.55"></path></svg>
            </button>
          </div>
          <div class="control-center">
            <span class="time-display" id="timeDisplay">0:00 / 0:00</span>
          </div>
          <div class="control-right">
            <button class="control-btn" id="muteBtn" aria-label="Mute/Unmute">
              <svg class="volume-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
              <svg class="mute-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>
            </button>
            <div class="volume-slider-container">
              <input type="range" id="volumeSlider" min="0" max="1" step="0.1" value="1" class="volume-slider" aria-label="Volume">
            </div>
            <button class="control-btn" id="fullscreenBtn" aria-label="Fullscreen">
              <svg class="fullscreen-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"></rect></svg>
              <svg class="exit-fullscreen-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18v3a2 2 0 0 1-2 2H5m18-18v3m0-18h3"></path></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Episode Info -->
    <div class="player-episode-info">
      <a href="season.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>" class="back-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        <span><?= htmlspecialchars($showData['title']) ?></span>
      </a>
      
      <div class="episode-breadcrumbs">
        <a href="show.php?id=<?= $showId ?>"><?= htmlspecialchars($showData['title']) ?></a>
        <span class="separator">/</span>
        <a href="season.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>">Season <?= $seasonNumber ?></a>
        <span class="separator">/</span>
        <span class="current">Episode <?= $episodeNumber ?>: <?= htmlspecialchars($episodeData['name']) ?></span>
      </div>
      
      <h1 class="player-episode-title"><?= htmlspecialchars($episodeData['name'] ?? 'Episode ' . $episodeNumber) ?></h1>
      
      <div class="player-episode-meta">
        <?php if (!empty($episodeData['air_date'])): ?>
          <span>Aired: <?= htmlspecialchars($episodeData['air_date']) ?></span>
        <?php endif; ?>
        <?php if (!empty($episodeData['runtime'])): ?>
          <span>•</span>
          <span><?= (int)$episodeData['runtime'] ?> min</span>
        <?php endif; ?>
        <span>S<?= str_pad($seasonNumber, 2, '0', STR_PAD_LEFT) ?>E<?= str_pad($episodeNumber, 2, '0', STR_PAD_LEFT) ?></span>
      </div>
      
      <p class="player-episode-overview"><?= htmlspecialchars($episodeData['overview'] ?? 'No description available.') ?></p>
    </div>

    <!-- Next Up / Auto-play -->
    <?php if ($nextEpisode): ?>
      <div class="next-up-player">
        <header class="section-header">
          <h2 class="section-title">Up Next</h2>
        </header>
        <a href="player.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>&episode=<?= $nextEpisode['episode_number'] ?>" class="next-up-player-card">
          <div class="next-up-player-thumb">
            <?php if (!empty($nextEpisode['still_url'])): ?>
              <img src="<?= htmlspecialchars($nextEpisode['still_url']) ?>" alt="" loading="lazy">
            <?php else: ?>
              <div class="next-up-placeholder"></div>
            <?php endif; ?>
          </div>
          <div class="next-up-info">
            <span class="next-up-label">Playing Next in <span id="countdown">15</span>s</span>
            <h3 class="next-up-title"><?= htmlspecialchars($nextEpisode['name']) ?></h3>
            <p class="next-up-meta">Episode <?= $nextEpisode['episode_number'] ?> • <?= (int)($nextEpisode['runtime'] ?? 0) ?> min</p>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <!-- Episode List Sidebar (collapsible on mobile) -->
    <aside class="episode-sidebar" id="episodeSidebar">
      <div class="sidebar-header">
        <h3>Episodes</h3>
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle episodes">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
      </div>
      <div class="episodes-list" id="episodesList">
        <?php foreach ($allEpisodes as $ep): ?>
          <a href="player.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>&episode=<?= $ep['episode_number'] ?>" class="sidebar-episode <?= ($ep['episode_number'] ?? 0) === $episodeNumber ? 'active' : '' ?>">
            <span class="sidebar-ep-num">E<?= str_pad($ep['episode_number'] ?? 0, 2, '0', STR_PAD_LEFT) ?></span>
            <span class="sidebar-ep-title"><?= htmlspecialchars($ep['name'] ?? 'Episode ' . ($ep['episode_number'] ?? 0)) ?></span>
            <span class="sidebar-ep-duration"><?= (int)($ep['runtime'] ?? 0) ?>m</span>
          </a>
        <?php endforeach; ?>
      </div>
    </aside>
  </section>
</main>

<script src="assets/js/player.js" defer></script>
<?php include __DIR__ . '/includes/footer.php'; ?>