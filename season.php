<?php
/**
 * season.php — Season episode browser
 */

$page = 'shows';
$pageTitle = 'Season — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$showId = (int)($_GET['show_id'] ?? 0);
$seasonNumber = (int)($_GET['season'] ?? 1);

if (!$showId) {
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

// Fetch season episodes
$seasonData = $tmdb->getSeasonEpisodes($showId, $seasonNumber);
$episodes = [];

if ($seasonData && !empty($seasonData['episodes'])) {
    foreach ($seasonData['episodes'] as $ep) {
        $episodes[] = $tmdb->formatEpisode($ep);
    }
}

// Get all seasons for selector
$seasons = $tmdb->getTvSeasons($showId);
$allSeasons = [];
if ($seasons && !empty($seasons['seasons'])) {
    foreach ($seasons['seasons'] as $s) {
        if (($s['season_number'] ?? 0) > 0) {
            $allSeasons[] = $tmdb->formatSeason($s);
        }
    }
}

$pageTitle = $showData['title'] . ' — Season ' . $seasonNumber . ' — Cartoon Universe';

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card show-detail">
    <!-- Season Header -->
    <div class="season-header">
      <a href="show.php?id=<?= $showId ?>" class="back-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span><?= htmlspecialchars($showData['title']) ?></span>
      </a>
      
      <div class="season-info">
        <h1 class="season-title">Season <?= $seasonNumber ?></h1>
        <?php 
        $currentSeason = array_filter($allSeasons, fn($s) => ($s['season_number'] ?? 0) === $seasonNumber);
        $currentSeason = array_values($currentSeason);
        if (!empty($currentSeason)): 
          $s = $currentSeason[0];
        ?>
          <p class="season-meta">
            <?php if (!empty($s['episode_count'])): ?>
              <span><?= (int)$s['episode_count'] ?> Episodes</span>
            <?php endif; ?>
            <?php if (!empty($s['air_date'])): ?>
              <span>•</span>
              <span>Aired: <?= htmlspecialchars($s['air_date']) ?></span>
            <?php endif; ?>
            <?php if (!empty($s['overview'])): ?>
              <p class="season-overview"><?= htmlspecialchars($s['overview']) ?></p>
            <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- Season Selector -->
      <div class="season-selector">
        <label for="seasonSelect" class="visually-hidden">Select Season</label>
        <select id="seasonSelect" onchange="window.location.href=this.value">
          <?php foreach ($allSeasons as $s): ?>
            <option value="season.php?show_id=<?= $showId ?>&season=<?= $s['season_number'] ?>" <?= ($s['season_number'] ?? 0) === $seasonNumber ? 'selected' : '' ?>>
              Season <?= (int)($s['season_number'] ?? 0) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Episodes List -->
    <?php if (empty($episodes)): ?>
      <div class="empty-state">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
          <circle cx="12" cy="12" r="4"></circle>
        </svg>
        <h3>No episodes found</h3>
        <p>No episodes available for this season.</p>
      </div>
    <?php else: ?>
      <div class="episodes-list" data-stagger>
        <?php foreach ($episodes as $ep): ?>
          <article class="episode-row">
            <div class="episode-thumbnail">
              <?php if (!empty($ep['still_url'])): ?>
                <img src="<?= htmlspecialchars($ep['still_url']) ?>" alt="" loading="lazy">
              <?php else: ?>
                <div class="episode-placeholder">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
                    <circle cx="12" cy="12" r="4"></circle>
                  </svg>
                </div>
              <?php endif; ?>
              <span class="episode-badge">S<?= str_pad($seasonNumber, 2, '0', STR_PAD_LEFT) ?>E<?= str_pad($ep['episode_number'] ?? 0, 2, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="episode-info">
              <div class="episode-header">
                <h3 class="episode-title"><?= htmlspecialchars($ep['name'] ?? 'Episode ' . ($ep['episode_number'] ?? 0)) ?></h3>
                <span class="show-title"><?= htmlspecialchars($showData['title']) ?></span>
              </div>
              <p class="episode-overview"><?= htmlspecialchars($ep['overview'] ?? 'No description available.') ?></p>
              <div class="episode-meta">
                <?php if (!empty($ep['air_date'])): ?>
                  <span class="meta-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Aired: <?= htmlspecialchars($ep['air_date']) ?></span>
                  </span>
                <?php endif; ?>
                <?php if (!empty($ep['runtime'])): ?>
                  <span class="meta-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span><?= (int)$ep['runtime'] ?> min</span>
                  </span>
                <?php endif; ?>
                <span class="meta-item">
                  <span class="rating">★ <?= number_format($ep['vote_average'] ?? 0, 1) ?></span>
                </span>
              </div>
              <div class="episode-actions">
                <?php if ($currentUser): ?>
                  <button class="btn-pill watch-ep-btn" data-episode-id="<?= $ep['id'] ?>" data-show-id="<?= $showId ?>" data-season="<?= $seasonNumber ?>" data-episode="<?= $ep['episode_number'] ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                    <span>WATCH</span>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>