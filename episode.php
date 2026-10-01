<?php
/**
 * episode.php — Episode detail page with player
 */

$page = 'episodes';
$pageTitle = 'Episode — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$showId = (int)($_GET['show_id'] ?? 0);
$seasonNumber = (int)($_GET['season'] ?? 1);
$episodeNumber = (int)($_GET['episode'] ?? 1);

if (!$showId || !$seasonNumber || !$episodeNumber) {
    header('Location: shows.php');
    exit;
}

// Fetch show details
$show = $tmdb->getTvDetails($showId);
if (!$show) {
    header('Location: shows.php');
    exit;
}

$showData = $tmdb->formatTvShow($show);

// Fetch episode details
$episode = $tmdb->getEpisodeDetails($showId, $seasonNumber, $episodeNumber);
if (!$episode) {
    header("Location: season.php?show_id=$showId&season=$seasonNumber");
    exit;
}

$episodeData = $tmdb->formatEpisode($episode);

// Fetch season info for navigation
$seasonData = $tmdb->getSeasonEpisodes($showId, $seasonNumber);
$allEpisodes = [];
if ($seasonData && !empty($seasonData['episodes'])) {
    foreach ($seasonData['episodes'] as $ep) {
        $allEpisodes[] = $tmdb->formatEpisode($ep);
    }
}

// Find prev/next episodes
$currentIndex = array_search($episodeNumber, array_column($allEpisodes, 'episode_number'));
$prevEpisode = $currentIndex > 0 ? $allEpisodes[$currentIndex - 1] : null;
$nextEpisode = $currentIndex !== false && $currentIndex < count($allEpisodes) - 1 ? $allEpisodes[$currentIndex + 1] : null;

// Check if user has favorited/liked this episode (optional)
$isFavorite = false;
$isLiked = false;
$likeCount = 0;
if ($currentUser) {
    $isFavorite = isFavorite(getConnection(), $currentUser['id'], $episodeData['id'], 'episode');
    $isLiked = getLikeStatus(getConnection(), $currentUser['id'], $episodeData['id'], 'episode');
    $likeCount = getLikeCount(getConnection(), $episodeData['id'], 'episode');
}

$pageTitle = $showData['title'] . ' — S' . str_pad($seasonNumber, 2, '0', STR_PAD_LEFT) . 'E' . str_pad($episodeNumber, 2, '0', STR_PAD_LEFT) . ' — ' . $episodeData['name'] . ' — Cartoon Universe';

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card episode-detail">
    <!-- Episode Header -->
    <div class="episode-header">
      <a href="season.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>" class="back-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span><?= htmlspecialchars($showData['title']) ?></span>
      </a>
      
      <div class="episode-breadcrumbs">
        <a href="show.php?id=<?= $showId ?>"><?= htmlspecialchars($showData['title']) ?></a>
        <span class="separator">/</span>
        <a href="season.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>">Season <?= $seasonNumber ?></a>
        <span class="separator">/</span>
        <span class="current">Episode <?= $episodeNumber ?></span>
      </div>
    </div>

    <!-- Episode Hero -->
    <div class="episode-hero">
      <div class="episode-thumbnail">
        <?php if (!empty($episodeData['still_url'])): ?>
          <img src="<?= htmlspecialchars($episodeData['still_url']) ?>" alt="" loading="eager">
        <?php else: ?>
          <div class="episode-placeholder">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
              <circle cx="12" cy="12" r="4"></circle>
            </svg>
          </div>
        <?php endif; ?>
        <span class="episode-badge">S<?= str_pad($seasonNumber, 2, '0', STR_PAD_LEFT) ?>E<?= str_pad($episodeNumber, 2, '0', STR_PAD_LEFT) ?></span>
      </div>
      
      <div class="episode-main-info">
        <h1 class="episode-title"><?= htmlspecialchars($episodeData['name'] ?? 'Episode ' . $episodeNumber) ?></h1>
        
        <div class="episode-meta">
          <?php if (!empty($episodeData['air_date'])): ?>
            <span class="meta-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              <span>Aired: <?= htmlspecialchars($episodeData['air_date']) ?></span>
            </span>
          <?php endif; ?>
          <?php if (!empty($episodeData['runtime'])): ?>
            <span class="meta-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              <span><?= (int)$episodeData['runtime'] ?> min</span>
            </span>
          <?php endif; ?>
          <span class="meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
            <span>Episode <?= $episodeNumber ?></span>
          </span>
          <span class="meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Season <?= $seasonNumber ?></span>
          </span>
        </div>

        <p class="episode-overview"><?= htmlspecialchars($episodeData['overview'] ?? 'No description available.') ?></p>

        <div class="episode-actions">
          <button class="btn-primary watch-btn" data-show-id="<?= $showId ?>" data-season="<?= $seasonNumber ?>" data-episode="<?= $episodeNumber ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            <span>WATCH NOW</span>
          </button>
          <?php if ($currentUser): ?>
            <button class="btn-pill favorite-btn" data-episode-id="<?= $episodeData['id'] ?>" data-media-type="episode">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              <span>ADD TO FAVORITES</span>
            </button>
            <button class="btn-pill like-btn" data-episode-id="<?= $episodeData['id'] ?>" data-media-type="episode">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              <span class="like-count"><?= number_format($likeCount) ?></span>
            </button>
            <button class="btn-pill share-btn">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
              <span>SHARE</span>
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="episode-nav">
      <?php if ($prevEpisode): ?>
        <a href="episode.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>&episode=<?= $prevEpisode['episode_number'] ?>" class="nav-prev">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
          <div>
            <span class="nav-label">Previous Episode</span>
            <span class="nav-title">EP <?= str_pad($prevEpisode['episode_number'], 2, '0', STR_PAD_LEFT) ?>: <?= htmlspecialchars($prevEpisode['name']) ?></span>
          </div>
        </a>
      <?php endif; ?>
      
      <a href="season.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>" class="nav-all">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>
        <span>All Episodes</span>
      </a>
      
      <?php if ($nextEpisode): ?>
        <a href="episode.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>&episode=<?= $nextEpisode['episode_number'] ?>" class="nav-next">
          <div>
            <span class="nav-label">Next Episode</span>
            <span class="nav-title">EP <?= str_pad($nextEpisode['episode_number'], 2, '0', STR_PAD_LEFT) ?>: <?= htmlspecialchars($nextEpisode['name']) ?></span>
          </div>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
      <?php endif; ?>
    </nav>

    <!-- Episode Player Section -->
    <div class="episode-section">
      <header class="section-header">
        <h2 class="section-title">Watch Episode</h2>
      </header>
      
      <div class="video-player-container">
        <div class="video-player" id="videoPlayer">
          <div class="player-placeholder">
            <div class="player-message">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px; opacity: 0.5;">
                <polygon points="23 7 16 12 23 17 23 7"></polygon>
                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
              </svg>
              <h3>Video playback is currently unavailable.</h3>
              <p>We don't have a licensed video source for this episode.</p>
            </div>
          </div>
        </div>
        
        <div class="player-info">
          <h3><?= htmlspecialchars($episodeData['name']) ?></h3>
          <p><?= htmlspecialchars($episodeData['overview'] ?? 'No description available.') ?></p>
          <div class="player-meta">
            <span>Season <?= $seasonNumber ?> • Episode <?= $episodeNumber ?></span>
            <?php if (!empty($episodeData['air_date'])): ?>
              <span>•</span>
              <span>Aired <?= htmlspecialchars($episodeData['air_date']) ?></span>
            <?php endif; ?>
            <?php if (!empty($episodeData['runtime'])): ?>
              <span>•</span>
              <span><?= (int)$episodeData['runtime'] ?> min</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Next Up -->
    <?php if ($nextEpisode): ?>
      <div class="next-up">
        <header class="section-header">
          <h2 class="section-title">Up Next</h2>
        </header>
        <a href="episode.php?show_id=<?= $showId ?>&season=<?= $seasonNumber ?>&episode=<?= $nextEpisode['episode_number'] ?>" class="next-up-card">
          <div class="next-up-thumbnail">
            <?php if (!empty($nextEpisode['still_url'])): ?>
              <img src="<?= htmlspecialchars($nextEpisode['still_url']) ?>" alt="" loading="lazy">
            <?php else: ?>
              <div class="next-up-placeholder">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
                  <circle cx="12" cy="12" r="4"></circle>
                </svg>
              </div>
            <?php endif; ?>
          </div>
          <div class="next-up-info">
            <span class="next-up-label">Playing Next</span>
            <h3 class="next-up-title"><?= htmlspecialchars($nextEpisode['name']) ?></h3>
            <p class="next-up-meta">Episode <?= $nextEpisode['episode_number'] ?> • <?= (int)($nextEpisode['runtime'] ?? 0) ?> min</p>
          </div>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
      </div>
    <?php endif; ?>
  </section>
</main>

<script src="assets/js/player.js" defer></script>
<?php include __DIR__ . '/includes/footer.php'; ?>