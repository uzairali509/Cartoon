<?php
/**
 * categories.php — Browse by genre/category
 */

$page = 'categories';
$pageTitle = 'Categories — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$genre = $_GET['genre'] ?? '';
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$genreNames = [
    'Animation' => 16,
    'Family' => 10751,
    'Comedy' => 35,
    'Adventure' => 12,
    'Fantasy' => 14,
    'Action' => 28,
    'Sci-Fi & Fantasy' => 10765,
    'Kids' => 10762,
    'Drama' => 18,
    'Mystery' => 9648,
    'Superhero' => 10759,
];

$genreId = $genreNames[$genre] ?? 16;

$results = [];
$totalPages = 0;
$totalResults = 0;

if (!empty($genre)) {
    $tvResults = $tmdb->discoverAnimatedShows([
        'with_genres' => $genreId,
        'page' => $pageNum,
    ]);
    
    $movieResults = $tmdb->discoverAnimatedMovies([
        'with_genres' => $genreId,
        'page' => $pageNum,
    ]);
    
    $allResults = [];
    if ($tvResults && isset($tvResults['results'])) {
        foreach ($tvResults['results'] as $show) {
            $allResults[] = array_merge($tmdb->formatTvShow($show), ['media_type' => 'tv']);
        }
        $totalResults = $tvResults['total_results'] ?? 0;
        $totalPages = $tvResults['total_pages'] ?? 0;
    }
    
    if ($movieResults && isset($movieResults['results'])) {
        foreach ($movieResults['results'] as $movie) {
            $allResults[] = array_merge($tmdb->formatMovie($movie), ['media_type' => 'movie']);
        }
        if (($movieResults['total_results'] ?? 0) > $totalResults) {
            $totalResults = $movieResults['total_results'];
            $totalPages = $movieResults['total_pages'] ?? 0;
        }
    }
    
    shuffle($allResults);
    $results = array_slice($allResults, 0, $perPage);
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title"><?= htmlspecialchars($genre ?: 'Categories') ?></h1>
      <p class="page-sub">
        <?php if ($genre): ?>
          Explore <?= htmlspecialchars($genre) ?> cartoons and animated shows
        <?php else: ?>
          Browse all animation genres
        <?php endif; ?>
      </p>
    </header>

    <!-- Genre Filter Tabs -->
    <div class="filter-tabs" role="tablist" aria-label="Genre filters">
      <?php foreach ($genreNames as $name => $id): ?>
        <a href="categories.php?genre=<?= urlencode($name) ?>" 
           class="filter-tab <?= $genre === $name ? 'active' : '' ?>"
           role="tab" aria-selected="<?= $genre === $name ? 'true' : 'false' ?>">
          <?= htmlspecialchars($name) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($genre)): ?>
      <?php if (empty($results)): ?>
        <div class="empty-state">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
            <circle cx="12" cy="12" r="4"></circle>
          </svg>
          <h3>No results found</h3>
          <p>No cartoons found for this genre.</p>
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
                      $allGenreNames = [
                        16 => 'Animation', 10751 => 'Family', 10759 => 'Action & Adventure',
                        10762 => 'Kids', 10765 => 'Sci-Fi & Fantasy', 35 => 'Comedy',
                        18 => 'Drama', 12 => 'Adventure', 14 => 'Fantasy', 9648 => 'Mystery',
                        28 => 'Action', 10762 => 'Kids'
                      ];
                      foreach (array_slice($item['genre_ids'], 0, 3) as $gid): ?>
                        <?php if (isset($allGenreNames[$gid])): ?>
                          <span class="genre-badge"><?= $allGenreNames[$gid] ?></span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <nav class="pagination" aria-label="Category pagination">
            <?php if ($pageNum > 1): ?>
              <a href="?genre=<?= urlencode($genre) ?>&page=<?= $pageNum - 1 ?>" class="page-btn prev">← Prev</a>
            <?php endif; ?>
            
            <?php 
            $start = max(1, $pageNum - 2);
            $end = min($totalPages, $pageNum + 2);
            if ($start > 1) echo '<span class="page-ellipsis">…</span>';
            for ($i = $start; $i <= $end; $i++): ?>
              <a href="?genre=<?= urlencode($genre) ?>&page=<?= $i ?>" class="page-btn <?= $i === $pageNum ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($end < $totalPages) echo '<span class="page-ellipsis">…</span>'; ?>
            
            <?php if ($pageNum < $totalPages): ?>
              <a href="?genre=<?= urlencode($genre) ?>&page=<?= $pageNum + 1 ?>" class="page-btn next">Next →</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    <?php else: ?>
      <!-- Category landing page -->
      <div class="genre-grid" data-stagger>
        <?php foreach ($genreNames as $name => $id): ?>
          <a href="categories.php?genre=<?= urlencode($name) ?>" class="genre-card">
            <div class="genre-card-icon">
              <?php
              $icons = [
                'Animation' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>',
                'Family' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
                'Comedy' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>',
                'Adventure' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
                'Fantasy' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>',
                'Action' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="23 4 14 13 9 8 4 17"></polyline><line x1="20" y1="5" x2="20" y2="5.01"></line><line x1="4" y1="19" x2="4" y2="19.01"></line></svg>',
                'Sci-Fi & Fantasy' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><ellipse cx="12" cy="12" rx="10" ry="6"></ellipse><path d="M8 12l2 2 4-4"></path></svg>',
                'Kids' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2c-5.52 0-10 4.48-10 10s4.48 10 10 10 10-4.48 10-10-4.48-10-10-10z"></path><circle cx="8.5" cy="14" r="1.5"></circle><circle cx="15.5" cy="14" r="1.5"></circle><path d="M8 20c0-2.2 3.5-4 7-4s7 1.8 7 4"></path></svg>',
                'Drama' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22c5.52 0 10-4.48 10-10S17.52 2 12 2 2 6.48 2 12s4.48 10 10 10z"></path><path d="M8.5 10.5l1.5 3 3-3"></path><path d="M14.5 10.5l1.5 3 3-3"></path></svg>',
                'Mystery' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                'Superhero' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L4 5.5V11a8 8 0 0 0 16 0V5.5L12 2z"></path><line x1="12" y1="22" x2="12" y2="14"></line></svg>',
              ];
              echo $icons[$name] ?? $icons['Animation'];
              ?>
            </div>
            <span class="genre-card-name"><?= htmlspecialchars($name) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>