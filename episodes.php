<?php
/**
 * episodes.php — Episodes page with real TMDB data
 */

$page = 'episodes';
$pageTitle = 'Episodes — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$pageNum = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$episodes = [];
$totalPages = 0;
$totalResults = 0;

// Fetch airing today episodes
$airingToday = $tmdb->getTvAiringToday(1);
if ($airingToday && !empty($airingToday['results'])) {
    foreach ($airingToday['results'] as $show) {
        $showData = $tmdb->formatTvShow($show);
        // Get latest episode for each show
        $latestEpisode = $tmdb->getSeasonEpisodes($show['id'], $showData['number_of_seasons'] ?? 1);
        if ($latestEpisode && !empty($latestEpisode['episodes'])) {
            $latestEp = end($latestEpisode['episodes']);
            $epData = $tmdb->formatEpisode($latestEp);
            $epData['show_title'] = $showData['title'];
            $epData['show_poster_url'] = $showData['poster_url'];
            $episodes[] = $epData;
        }
    }
}

// Also get on the air shows for more episodes
$onTheAir = $tmdb->getTvOnTheAir(1);
if ($onTheAir && !empty($onTheAir['results'])) {
    foreach (array_slice($onTheAir['results'], 0, 5) as $show) {
        $showData = $tmdb->formatTvShow($show);
        $seasonEpisodes = $tmdb->getSeasonEpisodes($show['id'], $showData['number_of_seasons'] ?? 1);
        if ($seasonEpisodes && !empty($seasonEpisodes['episodes'])) {
            $latestEp = end($seasonEpisodes['episodes']);
            $epData = $tmdb->formatEpisode($latestEp);
            $epData['show_title'] = $showData['title'];
            $epData['show_poster_url'] = $showData['poster_url'];
            $episodes[] = $epData;
        }
    }
}

// Sort by air date descending
usort($episodes, function($a, $b) {
    return strtotime($b['air_date'] ?? '') - strtotime($a['air_date'] ?? '');
});

$episodes = array_slice($episodes, 0, $perPage);

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title">Episodes</h1>
      <p class="page-sub">Latest episodes from animated shows airing now</p>
    </header>

    <?php if (empty($episodes)): ?>
      <div class="empty-state">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
          <circle cx="12" cy="12" r="4"></circle>
        </svg>
        <h3>No episodes found</h3>
        <p>No recent episodes available at the moment</p>
      </div>
    <?php else: ?>
      <div class="episodes-list" data-stagger>
        <?php foreach ($episodes as $ep): ?>
          <article class="episode-row">
            <div class="episode-thumbnail">
              <?php if (!empty($ep['still_url'])): ?>
                <img src="<?= htmlspecialchars($ep['still_url']) ?>" alt="" loading="lazy">
              <?php elseif (!empty($ep['show_poster_url'])): ?>
                <img src="<?= htmlspecialchars($ep['show_poster_url']) ?>" alt="" loading="lazy">
              <?php else: ?>
                <div class="episode-placeholder">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
                    <circle cx="12" cy="12" r="4"></circle>
                  </svg>
                </div>
              <?php endif; ?>
              <span class="episode-badge">S<?= str_pad($ep['season_number'] ?? 1, 2, '0', STR_PAD_LEFT) ?>E<?= str_pad($ep['episode_number'] ?? 0, 2, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="episode-info">
              <div class="episode-header">
                <h3 class="episode-title"><?= htmlspecialchars($ep['name'] ?? 'Episode ' . ($ep['episode_number'] ?? 0)) ?></h3>
                <span class="show-title"><?= htmlspecialchars($ep['show_title'] ?? '') ?></span>
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
                <?php 
                $pdo = getConnection();
                $showTmdbId = $ep['show_id'] ?? 0;
                $seasonNum = $ep['season_number'] ?? 1;
                $episodeNum = $ep['episode_number'] ?? 1;
                $hasVideo = hasLocalVideo($pdo, $showTmdbId, 'tv', $seasonNum, $episodeNum);
                $watchUrl = $hasVideo ? "watch.php?show_id={$showTmdbId}&season={$seasonNum}&episode={$episodeNum}" : "show.php?id={$showTmdbId}";
                $watchText = $hasVideo ? 'WATCH NOW' : 'VIEW SHOW';
                $watchIcon = $hasVideo ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>' : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
                ?>
                  <a href="<?= $watchUrl ?>" class="btn-pill watch-ep-btn" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 14px; font-size: .8rem;">
                    <?= $watchIcon ?>
                    <span><?= $watchText ?></span>
                    <?php if ($hasVideo): ?>
                      <span style="background: rgba(255,255,255,0.2); padding: 1px 6px; border-radius: 999px; font-size: .55rem; font-weight: 700;">LOCAL</span>
                    <?php endif; ?>
                  </a>
                  <?php if ($currentUser): ?>
                    <button class="btn-pill favorite-btn" data-episode-id="<?= $ep['id'] ?>" data-media-type="episode">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                      <span>FAVORITE</span>
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