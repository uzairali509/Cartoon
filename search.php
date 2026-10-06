<?php
/**
 * search.php — Global search page
 */

$page = 'search';
$pageTitle = 'Search — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$query = trim($_GET['q'] ?? '');
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$results = [];
$totalResults = 0;
$totalPages = 0;

if (!empty($query)) {
    // Log search history for logged-in users
    if ($currentUser) {
        addSearchHistory(getConnection(), $currentUser['id'], $query);
    }

    // Search TMDB
    $tmdbResults = $tmdb->searchMulti($query, $pageNum);
    
    if ($tmdbResults && isset($tmdbResults['results'])) {
        $allResults = $tmdbResults['results'];
        $totalResults = $tmdbResults['total_results'] ?? 0;
        $totalPages = $tmdbResults['total_pages'] ?? 0;
        
        // Filter for animation/family content
        foreach ($allResults as $result) {
            $mediaType = $result['media_type'] ?? '';
            
            // Skip people for now (handle separately if needed)
            if ($mediaType === 'person') continue;
            
            // Filter for animation/family genres
            $genreIds = $result['genre_ids'] ?? [];
            $isAnimation = in_array(16, $genreIds); // Animation genre
            $isFamily = in_array(10751, $genreIds); // Family genre
            $isKids = in_array(10762, $genreIds); // Kids genre
            
            // Include if animation, family, kids, or has animation keywords
            $title = $result['name'] ?? $result['title'] ?? '';
            $overview = $result['overview'] ?? '';
            $hasAnimationKeywords = stripos($title, 'cartoon') !== false || 
                                   stripos($title, 'animated') !== false ||
                                   stripos($overview, 'cartoon') !== false ||
                                   stripos($overview, 'animated') !== false;
            
            if ($isAnimation || $isFamily || $isKids || $hasAnimationKeywords) {
                if ($mediaType === 'tv') {
                    $formatted = $tmdb->formatTvShow($result);
                    $formatted['media_type'] = 'tv';
                    $results[] = $formatted;
                } elseif ($mediaType === 'movie') {
                    $formatted = $tmdb->formatMovie($result);
                    $formatted['media_type'] = 'movie';
                    $results[] = $formatted;
                }
            }
        }
    }
    
    // If no results from filtered search, show all results
    if (empty($results) && isset($allResults)) {
        foreach ($allResults as $result) {
            $mediaType = $result['media_type'] ?? '';
            if ($mediaType === 'person') continue;
            
            if ($mediaType === 'tv') {
                $formatted = $tmdb->formatTvShow($result);
                $formatted['media_type'] = 'tv';
                $results[] = $formatted;
            } elseif ($mediaType === 'movie') {
                $formatted = $tmdb->formatMovie($result);
                $formatted['media_type'] = 'movie';
                $results[] = $formatted;
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title"><?= !empty($query) ? 'Search Results' : 'Search' ?></h1>
      <p class="page-sub">
        <?php if (!empty($query)): ?>
          Showing <?= count($results) ?> of <?= $totalResults ?> results for "<?= htmlspecialchars($query) ?>"
        <?php else: ?>
          Search for cartoons, shows, movies, and characters
        <?php endif; ?>
      </p>
    </header>

    <?php if (!empty($query)): ?>
      <?php if (empty($results)): ?>
        <div class="empty-state">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <h3>No results found</h3>
          <p>We couldn't find any cartoons matching "<strong><?= htmlspecialchars($query) ?></strong>"</p>
          <p class="empty-hint">Try different keywords or check spelling</p>
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
                      $genreNames = [
                        16 => 'Animation', 10751 => 'Family', 10759 => 'Action & Adventure',
                        10762 => 'Kids', 10765 => 'Sci-Fi & Fantasy', 35 => 'Comedy',
                        18 => 'Drama', 12 => 'Adventure', 14 => 'Fantasy', 9648 => 'Mystery'
                      ];
                      foreach (array_slice($item['genre_ids'], 0, 3) as $gid): ?>
                        <?php if (isset($genreNames[$gid])): ?>
                          <span class="genre-badge"><?= $genreNames[$gid] ?></span>
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
          <nav class="pagination" aria-label="Search results pagination">
            <?php if ($pageNum > 1): ?>
              <a href="?q=<?= urlencode($query) ?>&page=<?= $pageNum - 1 ?>" class="page-btn prev">← Prev</a>
            <?php endif; ?>
            
            <?php 
            $start = max(1, $pageNum - 2);
            $end = min($totalPages, $pageNum + 2);
            if ($start > 1) echo '<span class="page-ellipsis">…</span>';
            for ($i = $start; $i <= $end; $i++): ?>
              <a href="?q=<?= urlencode($query) ?>&page=<?= $i ?>" class="page-btn <?= $i === $pageNum ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($end < $totalPages) echo '<span class="page-ellipsis">…</span>'; ?>
            
            <?php if ($pageNum < $totalPages): ?>
              <a href="?q=<?= urlencode($query) ?>&page=<?= $pageNum + 1 ?>" class="page-btn next">Next →</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    <?php else: ?>
      <!-- Search landing page -->
      <div class="search-landing">
        <form action="search.php" method="GET" class="search-landing-form">
          <input type="search" name="q" placeholder="Search cartoons, shows, movies, characters..." 
                 autofocus autocomplete="off" required>
          <button type="submit" class="btn-primary">Search</button>
        </form>
        
        <div class="search-categories">
          <h3>Browse by Category</h3>
          <div class="genre-grid">
            <a href="categories.php?genre=Animation" class="genre-card">
              <div class="genre-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>
              </div>
              <span class="genre-card-name">Animation</span>
            </a>
            <a href="categories.php?genre=Family" class="genre-card">
              <div class="genre-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
              </div>
              <span class="genre-card-name">Family</span>
            </a>
            <a href="categories.php?genre=Comedy" class="genre-card">
              <div class="genre-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
              </div>
              <span class="genre-card-name">Comedy</span>
            </a>
            <a href="categories.php?genre=Adventure" class="genre-card">
              <div class="genre-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
              </div>
              <span class="genre-card-name">Adventure</span>
            </a>
            <a href="categories.php?genre=Fantasy" class="genre-card">
              <div class="genre-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
              </div>
              <span class="genre-card-name">Fantasy</span>
            </a>
            <a href="categories.php?genre=Action" class="genre-card">
              <div class="genre-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="23 4 14 13 9 8 4 17"></polyline><line x1="20" y1="5" x2="20" y2="5.01"></line><line x1="4" y1="19" x2="4" y2="19.01"></line></svg>
              </div>
              <span class="genre-card-name">Action</span>
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>