<?php
/**
 * shows.php — Shows page with real API data and filters
 */

$page = 'shows';
$pageTitle = 'Shows — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$apiError = '';
if (!$tmdb->isConfigured()) {
    $apiError = 'TMDB API key not configured. Please add your TMDB API key to the .env file.';
} else {
    $test = $tmdb->getTrendingTv('week', 1);
    if (isset($test['error']) && strpos($test['error'], '401') !== false) {
        $apiError = 'Invalid TMDB API key (401 Unauthorized). Please update your API key in the .env file.';
    }
}

$genre = $_GET['genre'] ?? '';
$year = $_GET['year'] ?? '';
$country = $_GET['country'] ?? '';
$rating = $_GET['rating'] ?? '';
$sort = $_GET['sort'] ?? 'popularity.desc';
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$results = [];
$totalPages = 0;
$totalResults = 0;

if (empty($apiError)) {
    // Build discover parameters
    $discoverParams = [
        'page' => $pageNum,
        'sort_by' => $sort,
        'include_adult' => false,
        'with_genres' => '16', // Animation
    ];

    $genreMap = [
        'Animation' => 16, 'Family' => 10751, 'Comedy' => 35,
        'Adventure' => 12, 'Fantasy' => 14, 'Action' => 28,
        'Sci-Fi & Fantasy' => 10765, 'Kids' => 10762, 'Drama' => 18,
        'Mystery' => 9648, 'Superhero' => 10759, 'Action & Adventure' => 10759,
    ];

    if ($genre && $genre !== 'All' && isset($genreMap[$genre])) {
        $discoverParams['with_genres'] = $genreMap[$genre] . ',16';
    }

    if ($year) {
        $discoverParams['first_air_date.gte'] = $year . '-01-01';
        $discoverParams['first_air_date.lte'] = $year . '-12-31';
    }

    if ($country) {
        $discoverParams['with_origin_country'] = strtoupper($country);
    }

    if ($rating) {
        $discoverParams['vote_average.gte'] = (float)$rating;
    }

    $tvResults = $tmdb->discoverAnimatedShows($discoverParams);
    if ($tvResults && isset($tvResults['results'])) {
        foreach ($tvResults['results'] as $show) {
            $results[] = array_merge($tmdb->formatTvShow($show), ['media_type' => 'tv']);
        }
        $totalResults = $tvResults['total_results'] ?? 0;
        $totalPages = $tvResults['total_pages'] ?? 0;
    }

    // Also get movies
    $movieParams = [
        'page' => $pageNum,
        'with_genres' => '16',
        'sort_by' => $sort,
        'include_adult' => false,
    ];

    if ($year) {
        $movieParams['primary_release_date.gte'] = $year . '-01-01';
        $movieParams['primary_release_date.lte'] = $year . '-12-31';
    }

    if ($country) {
        $movieParams['with_origin_country'] = strtoupper($country);
    }

    if ($rating) {
        $movieParams['vote_average.gte'] = (float)$rating;
    }

    $movieResults = $tmdb->discoverAnimatedMovies($movieParams);
    if ($movieResults && isset($movieResults['results'])) {
        foreach ($movieResults['results'] as $movie) {
            $results[] = array_merge($tmdb->formatMovie($movie), ['media_type' => 'movie']);
        }
        if (($movieResults['total_results'] ?? 0) > $totalResults) {
            $totalResults = $movieResults['total_results'];
            $totalPages = $movieResults['total_pages'] ?? 0;
        }
    }

    // Shuffle and paginate combined results
    shuffle($results);
    $results = array_slice($results, 0, $perPage);
} else {
    $results = [];
}

$years = range(date('Y'), 1980);
$countries = [
    'US' => 'United States', 'JP' => 'Japan', 'GB' => 'United Kingdom',
    'FR' => 'France', 'CA' => 'Canada', 'KR' => 'South Korea',
    'CN' => 'China', 'IN' => 'India', 'AU' => 'Australia',
    'DE' => 'Germany', 'ES' => 'Spain', 'IT' => 'Italy',
];

$sortOptions = [
    'popularity.desc' => 'Popular',
    'vote_average.desc' => 'Top Rated',
    'first_air_date.desc' => 'Newest',
    'name.asc' => 'A-Z',
];

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title">Shows</h1>
      <p class="page-sub">Discover animated TV shows and series</p>
    </header>

    <?php if (!empty($apiError)): ?>
      <div class="api-error">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        <h3>API Key Required</h3>
        <p><?= htmlspecialchars($apiError) ?></p>
        <a href="https://www.themoviedb.org/settings/api" target="_blank" class="btn-primary">Get TMDB API Key</a>
      </div>
    <?php else: ?>
      <!-- Filter Bar -->
      <div class="filter-bar">
        <form method="GET" class="filter-form" id="filterForm">
          <div class="filter-group">
            <label for="genreFilter" class="visually-hidden">Genre</label>
            <select name="genre" id="genreFilter" onchange="this.form.submit()">
              <option value="">All Genres</option>
              <?php foreach (array_keys($genreMap) as $name): ?>
                <option value="<?= urlencode($name) ?>" <?= $genre === $name ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="filter-group">
            <label for="yearFilter" class="visually-hidden">Year</label>
            <select name="year" id="yearFilter" onchange="this.form.submit()">
              <option value="">All Years</option>
              <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="filter-group">
            <label for="countryFilter" class="visually-hidden">Country</label>
            <select name="country" id="countryFilter" onchange="this.form.submit()">
              <option value="">All Countries</option>
              <?php foreach ($countries as $code => $name): ?>
                <option value="<?= $code ?>" <?= $country === $code ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="filter-group">
            <label for="ratingFilter" class="visually-hidden">Minimum Rating</label>
            <select name="rating" id="ratingFilter" onchange="this.form.submit()">
              <option value="">Any Rating</option>
              <option value="8" <?= $rating == '8' ? 'selected' : '' ?>>8.0+</option>
              <option value="7" <?= $rating == '7' ? 'selected' : '' ?>>7.0+</option>
              <option value="6" <?= $rating == '6' ? 'selected' : '' ?>>6.0+</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label for="sortFilter" class="visually-hidden">Sort By</label>
            <select name="sort" id="sortFilter" onchange="this.form.submit()">
              <?php foreach ($sortOptions as $value => $label): ?>
                <option value="<?= $value ?>" <?= $sort === $value ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <?php if ($genre || $year || $country || $rating): ?>
            <a href="shows.php" class="btn-pill filter-clear">Clear Filters</a>
          <?php endif; ?>
        </form>
      </div>

      <?php if (empty($results)): ?>
        <div class="empty-state">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
            <circle cx="12" cy="12" r="4"></circle>
          </svg>
          <h3>No shows found</h3>
          <p>Try adjusting your filters</p>
          <a href="shows.php" class="btn-primary">Clear All Filters</a>
        </div>
      <?php else: ?>
        <div class="content-grid" data-stagger>
          <?php foreach ($results as $item): 
            $pdo = getConnection();
            $hasVideo = hasLocalVideo($pdo, $item['tmdb_id'], $item['media_type']);
            $watchUrl = $hasVideo ? "watch.php?show_id={$item['tmdb_id']}" : ($item['media_type'] === 'tv' ? "show.php?id={$item['tmdb_id']}" : "movie.php?id={$item['tmdb_id']}");
            $watchText = $hasVideo ? 'WATCH NOW' : 'VIEW DETAILS';
            $watchIcon = $hasVideo ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>' : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
          ?>
            <article class="content-card">
              <a href="<?= $item['media_type'] === 'tv' ? "show.php?id={$item['tmdb_id']}" : "movie.php?id={$item['tmdb_id']}" ?>" class="card-link">
                <div class="card-poster">
                  <?php if (!empty($item['poster_url'])): ?>
                    <img src="<?= htmlspecialchars($item['poster_url']) ?>" alt="" loading="lazy">
                  <?php else: ?>
                    <div class="poster-placeholder">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
                        <circle cx="12" cy="12" r="4"></circle>
                      </svg>
                    </div>
                  <?php endif; ?>
                  <span class="card-type"><?= htmlspecialchars($item['media_type'] === 'tv' ? 'TV Show' : 'Movie') ?></span>
                  <?php if ($hasVideo): ?>
                    <span class="card-video-badge">▶ PLAY</span>
                  <?php endif; ?>
                </div>
                <div class="card-info">
                  <h3 class="card-title"><?= htmlspecialchars($item['title']) ?></h3>
                  <div class="card-meta">
                    <?php if (!empty($item['first_air_date']) || !empty($item['release_date'])): ?>
                      <span class="year"><?= htmlspecialchars(substr($item['first_air_date'] ?? $item['release_date'], 0, 4)) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($item['vote_average'])): ?>
                      <span class="rating">★ <?= number_format($item['vote_average'], 1) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($item['origin_country'])): ?>
                      <span class="country"><?= htmlspecialchars(strtoupper($item['origin_country'][0])) ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($item['genre_ids'])): ?>
                    <div class="card-genres">
                      <?php 
                      foreach (array_slice($item['genre_ids'], 0, 3) as $gid): ?>
                        <?php if (isset($genreMap[$gid])): ?>
                          <span class="genre-badge"><?= $genreMap[$gid] ?></span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </a>
              <a href="<?= $watchUrl ?>" class="card-watch-btn" style="width: 100%; margin-top: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: <?= $hasVideo ? 'var(--accent-primary)' : 'var(--bg-secondary)' ?>; color: <?= $hasVideo ? '#fff' : 'var(--text-primary)' ?>; border: 1px solid <?= $hasVideo ? 'var(--accent-primary)' : 'var(--border-default)' ?>; border-radius: var(--radius-md); font-weight: 600; font-size: .85rem; text-decoration: none; transition: var(--transition-fast);">
                <?= $watchIcon ?>
                <span><?= $watchText ?></span>
              </a>
            </article>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <nav class="pagination" aria-label="Shows pagination">
            <?php if ($pageNum > 1): ?>
              <a href="<?= http_build_query(array_merge($_GET, ['page' => $pageNum - 1])) ?>" class="page-btn prev">← Prev</a>
            <?php endif; ?>
            
            <?php 
            $start = max(1, $pageNum - 2);
            $end = min($totalPages, $pageNum + 2);
            if ($start > 1) echo '<span class="page-ellipsis">…</span>';
            for ($i = $start; $i <= $end; $i++): ?>
              <a href="<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-btn <?= $i === $pageNum ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($end < $totalPages) echo '<span class="page-ellipsis">…</span>'; ?>
            
            <?php if ($pageNum < $totalPages): ?>
              <a href="<?= http_build_query(array_merge($_GET, ['page' => $pageNum + 1])) ?>" class="page-btn next">Next →</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>