<?php
/**
 * show.php — Show/TV Series detail page
 */

$page = 'shows';
$pageTitle = 'Show Details — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$showId = (int)($_GET['id'] ?? 0);
if (!$showId) {
    header('Location: shows.php');
    exit;
}

// Fetch show details from TMDB
$show = $tmdb->getTvDetails($showId);
if (!$show) {
    // Show not found
    include __DIR__ . '/includes/header.php';
    ?>
    <main class="page-wrap">
      <section class="page-card">
        <div class="empty-state" style="padding: 60px 20px; text-align: center;">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 20px; opacity: 0.5;">
            <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
            <circle cx="12" cy="12" r="4"></circle>
          </svg>
          <h3 class="page-title">Show Not Found</h3>
          <p style="color: var(--text-secondary);">The show you're looking for doesn't exist or has been removed.</p>
          <a href="shows.php" class="btn-primary" style="margin-top: 20px; display: inline-block;">← Back to Shows</a>
        </div>
      </section>
    </main>
    <?php include __DIR__ . '/includes/footer.php';
    exit;
}

$showData = $tmdb->formatTvShow($show);
$seasons = $tmdb->getTvSeasons($showId);
$credits = $tmdb->getTvCredits($showId);
$videos = $tmdb->getTvVideos($showId);
$similar = $tmdb->getSimilarTvShows($showId, 1);
$recommendations = $tmdb->getTvRecommendations($showId, 1);

// Check if user has favorited/liked
$isFavorite = false;
$isLiked = false;
if ($currentUser) {
    $isFavorite = isFavorite(getConnection(), $currentUser['id'], $showId, 'tv');
    $isLiked = getLikeStatus(getConnection(), $currentUser['id'], $showId, 'tv');
}
$likeCount = getLikeCount(getConnection(), $showId, 'tv');

$pageTitle = $showData['title'] . ' — Cartoon Universe';

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card show-detail">
    <!-- Hero/Backdrop -->
    <div class="show-hero">
      <?php if (!empty($showData['backdrop_url'])): ?>
        <img src="<?= htmlspecialchars($showData['backdrop_url']) ?>" alt="" class="show-backdrop" loading="eager">
      <?php else: ?>
        <div class="show-backdrop placeholder"></div>
      <?php endif; ?>
      <div class="show-hero-gradient"></div>
      <div class="show-hero-content">
        <div class="show-poster-wrapper">
          <?php if (!empty($showData['poster_url'])): ?>
            <img src="<?= htmlspecialchars($showData['poster_url']) ?>" alt="" class="show-poster" loading="eager">
          <?php else: ?>
            <div class="show-poster placeholder">
              <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
                <circle cx="12" cy="12" r="4"></circle>
              </svg>
            </div>
          <?php endif; ?>
        </div>
        <div class="show-header-info">
          <div class="show-badges">
            <?php if (!empty($showData['status'])): ?>
              <span class="badge status-<?= strtolower(str_replace(' ', '-', $showData['status'])) ?>"><?= htmlspecialchars($showData['status']) ?></span>
            <?php endif; ?>
            <?php if (!empty($showData['vote_average'])): ?>
              <span class="badge rating">★ <?= number_format($showData['vote_average'], 1) ?></span>
            <?php endif; ?>
            <?php if (!empty($showData['first_air_date'])): ?>
              <span class="badge year"><?= htmlspecialchars(substr($showData['first_air_date'], 0, 4)) ?></span>
            <?php endif; ?>
            <?php if (!empty($showData['origin_country'])): ?>
              <span class="badge country"><?= htmlspecialchars(strtoupper($showData['origin_country'][0])) ?></span>
            <?php endif; ?>
          </div>
          <h1 class="show-title"><?= htmlspecialchars($showData['title']) ?></h1>
          <?php if (!empty($showData['original_title']) && $showData['original_title'] !== $showData['title']): ?>
            <p class="show-original-title"><?= htmlspecialchars($showData['original_title']) ?></p>
          <?php endif; ?>
          <div class="show-meta">
            <?php if (!empty($showData['genre_ids'])): ?>
              <div class="genres">
                <?php 
                $genreMap = [
                    16 => 'Animation', 10751 => 'Family', 10759 => 'Action & Adventure',
                    10762 => 'Kids', 10765 => 'Sci-Fi & Fantasy', 35 => 'Comedy',
                    18 => 'Drama', 12 => 'Adventure', 14 => 'Fantasy', 9648 => 'Mystery',
                    28 => 'Action', 10759 => 'Action & Adventure'
                ];
                foreach (array_slice($showData['genre_ids'], 0, 4) as $gid): ?>
                  <?php if (isset($genreMap[$gid])): ?>
                    <span class="genre-tag"><?= $genreMap[$gid] ?></span>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="meta-items">
              <?php if (!empty($showData['first_air_date'])): ?>
                <span class="meta-item">Premiered: <?= htmlspecialchars($showData['first_air_date']) ?></span>
              <?php endif; ?>
              <?php if (!empty($showData['last_air_date'])): ?>
                <span class="meta-item">Ended: <?= htmlspecialchars($showData['last_air_date']) ?></span>
              <?php endif; ?>
              <?php if (!empty($showData['number_of_seasons'])): ?>
                <span class="meta-item"><?= (int)$showData['number_of_seasons'] ?> Seasons</span>
              <?php endif; ?>
              <?php if (!empty($showData['number_of_episodes'])): ?>
                <span class="meta-item"><?= (int)$showData['number_of_episodes'] ?> Episodes</span>
              <?php endif; ?>
              <?php if (!empty($showData['episode_run_time'][0])): ?>
                <span class="meta-item"><?= (int)$showData['episode_run_time'][0] ?> min/ep</span>
              <?php endif; ?>
              <?php if (!empty($showData['origin_country'])): ?>
                <span class="meta-item">Country: <?= htmlspecialchars(implode(', ', array_map('strtoupper', $showData['origin_country']))) ?></span>
              <?php endif; ?>
              <?php if (!empty($showData['original_language'])): ?>
                <span class="meta-item">Language: <?= htmlspecialchars(strtoupper($showData['original_language'])) ?></span>
              <?php endif; ?>
              <?php if (!empty($showData['status'])): ?>
                <span class="meta-item status-<?= strtolower($showData['status']) ?>"><?= htmlspecialchars($showData['status']) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="show-actions">
            <button class="btn-primary watch-btn" data-show-id="<?= $showId ?>">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
              <span>WATCH NOW</span>
            </button>
            <button class="btn-pill trailer-btn" data-show-id="<?= $showId ?>">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
              <span>TRAILER</span>
            </button>
            <?php if ($currentUser): ?>
              <button class="btn-pill favorite-btn <?= $isFavorite ? 'active' : '' ?>" data-show-id="<?= $showId ?>" data-media-type="tv">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="<?= $isFavorite ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                <span><?= $isFavorite ? 'FAVORITED' : 'ADD TO FAVORITES' ?></span>
              </button>
              <button class="btn-pill like-btn <?= $isLiked ? 'active' : '' ?>" data-show-id="<?= $showId ?>" data-media-type="tv">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="<?= $isLiked ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                <span class="like-count"><?= number_format($likeCount) ?></span>
              </button>
              <button class="btn-pill share-btn" data-show-id="<?= $showId ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                <span>SHARE</span>
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Overview -->
    <div class="show-section">
      <h2 class="section-title">Overview</h2>
      <p class="show-overview"><?= htmlspecialchars($showData['overview'] ?? 'No overview available.') ?></p>
      
      <?php if (!empty($showData['tagline'])): ?>
        <p class="show-tagline">"<?= htmlspecialchars($showData['tagline']) ?>"</p>
      <?php endif; ?>
    </div>

    <!-- Cast/Characters -->
    <?php if ($credits && !empty($credits['cast'])): ?>
      <div class="show-section">
        <header class="section-header">
          <h2 class="section-title">Cast & Characters</h2>
        </header>
        <div class="cast-carousel" data-carousel>
          <div class="carousel-track">
            <?php foreach (array_slice($credits['cast'], 0, 12) as $actor): ?>
              <article class="cast-card">
                <?php if (!empty($actor['profile_path'])): ?>
                  <img src="<?= $tmdb->getProfileUrl($actor['profile_path']) ?>" alt="" loading="lazy">
                <?php else: ?>
                  <div class="cast-placeholder">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <circle cx="12" cy="8" r="4"></circle>
                      <path d="M12 14c-4.42 0-8 3.58-8 8v2h16v-2c0-4.42-3.58-8-8-8z"></path>
                    </svg>
                  </div>
                <?php endif; ?>
                <h4 class="cast-name"><?= htmlspecialchars($actor['name']) ?></h4>
                <p class="cast-character">as <?= htmlspecialchars($actor['character'] ?? 'Unknown') ?></p>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Trailer -->
    <?php if ($videos && !empty($videos['results'])): ?>
      <?php $trailer = array_filter($videos['results'], fn($v) => ($v['type'] ?? '') === 'Trailer' && ($v['site'] ?? '') === 'YouTube'); ?>
      <?php if ($trailer): ?>
        <?php $trailer = array_values($trailer)[0]; ?>
        <div class="show-section">
          <header class="section-header">
            <h2 class="section-title">Trailer</h2>
          </header>
          <div class="video-wrapper">
            <iframe 
              src="https://www.youtube.com/embed/<?= htmlspecialchars($trailer['key']) ?>"
              title="<?= htmlspecialchars($trailer['name']) ?>"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
              loading="lazy">
            </iframe>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Seasons -->
    <?php if ($seasons && !empty($seasons['seasons'])): ?>
      <div class="show-section">
        <header class="section-header">
          <h2 class="section-title">Seasons</h2>
        </header>
        <div class="seasons-grid">
          <?php foreach ($seasons['seasons'] as $season): ?>
            <?php if (($season['season_number'] ?? 0) === 0) continue; // Skip specials ?>
            <?php 
              $seasonData = $tmdb->formatSeason($season);
            ?>
            <article class="season-card">
              <a href="season.php?show_id=<?= $showId ?>&season=<?= $seasonData['season_number'] ?>" class="season-link">
                <div class="season-poster">
                  <?php if (!empty($seasonData['poster_url'])): ?>
                    <img src="<?= htmlspecialchars($seasonData['poster_url']) ?>" alt="" loading="lazy">
                  <?php else: ?>
                    <div class="season-placeholder">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
                        <circle cx="12" cy="12" r="4"></circle>
                      </svg>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="season-info">
                  <h4 class="season-title"><?= htmlspecialchars($seasonData['name'] ?? "Season {$seasonData['season_number']}") ?></h4>
                  <p class="season-meta">
                    <span><?= (int)($seasonData['episode_count'] ?? 0) ?> Episodes</span>
                    <?php if (!empty($seasonData['air_date'])): ?>
                      <span>•</span>
                      <span><?= htmlspecialchars($seasonData['air_date']) ?></span>
                    <?php endif; ?>
                  </p>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Similar Shows -->
    <?php if ($similar && !empty($similar['results'])): ?>
      <div class="show-section">
        <header class="section-header">
          <h2 class="section-title">Similar Shows</h2>
        </header>
        <div class="content-carousel" data-carousel>
          <div class="carousel-track">
            <?php foreach (array_slice($similar['results'], 0, 10) as $show): ?>
              <?php 
                $formatted = $tmdb->formatTvShow($show);
                echo renderShowCard($formatted, true);
              ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Recommendations -->
    <?php if ($recommendations && !empty($recommendations['results'])): ?>
      <div class="show-section">
        <header class="section-header">
          <h2 class="section-title">Recommendations</h2>
        </header>
        <div class="content-carousel" data-carousel>
          <div class="carousel-track">
            <?php foreach (array_slice($recommendations['results'], 0, 10) as $show): ?>
              <?php 
                $formatted = $tmdb->formatTvShow($show);
                echo renderShowCard($formatted, true);
              ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>