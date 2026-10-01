<?php
/**
 * shows.php — Shows page with real API data and filters
 */

$page = 'shows';
$pageTitle = 'Shows — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$genre = $_GET['genre'] ?? '';
$year = $_GET['year'] ?? '';
$country = $_GET['country'] ?? '';
$rating = $_GET['rating'] ?? '';
$sort = $_GET['sort'] ?? 'popularity.desc';
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

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

$results = [];
$totalPages = 0;
$totalResults = 0;

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
        <?php foreach ($results as $item): ?>
          <article class="content-card">
            <a href="<?= $item['media_type'] === 'tv' ? 'show.php?id=' . $item['tmdb_id'] : 'movie.php?id=' . $item['tmdb_id'] ?>" class="card-link">
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
              <?php if ($currentUser): ?>
                <button class="favorite-btn" data-tmdb-id="<?= $item['tmdb_id'] ?>" data-media-type="<?= $item['media_type'] ?>" aria-label="Add to favorites">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </button>
              <?php endif; ?>
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
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>