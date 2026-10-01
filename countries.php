<?php
/**
 * countries.php — Browse cartoons by country
 */

$page = 'countries';
$pageTitle = 'Countries — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$country = $_GET['country'] ?? '';
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

// Common country codes for animation
$countries = [
    'US' => ['name' => 'United States', 'flag' => '🇺🇸'],
    'JP' => ['name' => 'Japan', 'flag' => '🇯🇵'],
    'GB' => ['name' => 'United Kingdom', 'flag' => '🇬🇧'],
    'FR' => ['name' => 'France', 'flag' => '🇫🇷'],
    'CA' => ['name' => 'Canada', 'flag' => '🇨🇦'],
    'KR' => ['name' => 'South Korea', 'flag' => '🇰🇷'],
    'CN' => ['name' => 'China', 'flag' => '🇨🇳'],
    'IN' => ['name' => 'India', 'flag' => '🇮🇳'],
    'AU' => ['name' => 'Australia', 'flag' => '🇦🇺'],
    'DE' => ['name' => 'Germany', 'flag' => '🇩🇪'],
    'ES' => ['name' => 'Spain', 'flag' => '🇪🇸'],
    'IT' => ['name' => 'Italy', 'flag' => '🇮🇹'],
    'BR' => ['name' => 'Brazil', 'flag' => '🇧🇷'],
    'MX' => ['name' => 'Mexico', 'flag' => '🇲🇽'],
    'RU' => ['name' => 'Russia', 'flag' => '🇷🇺'],
];

$results = [];
$totalPages = 0;
$totalResults = 0;

if (!empty($country) && isset($countries[$country])) {
    $tvResults = $tmdb->discoverTvByCountry($country, [
        'page' => $pageNum,
        'with_genres' => '16', // Animation
    ]);
    
    if ($tvResults && isset($tvResults['results'])) {
        foreach ($tvResults['results'] as $show) {
            $results[] = array_merge($tmdb->formatTvShow($show), ['media_type' => 'tv']);
        }
        $totalResults = $tvResults['total_results'] ?? 0;
        $totalPages = $tvResults['total_pages'] ?? 0;
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title"><?= $country ? htmlspecialchars($countries[$country]['name']) : 'Countries' ?></h1>
      <p class="page-sub">
        <?php if ($country): ?>
          Cartoons and animated shows from <?= htmlspecialchars($countries[$country]['name']) ?>
        <?php else: ?>
          Browse cartoons by country of origin
        <?php endif; ?>
      </p>
    </header>

    <!-- Country Filter Tabs -->
    <div class="filter-tabs" role="tablist" aria-label="Country filters">
      <?php foreach ($countries as $code => $data): ?>
        <a href="countries.php?country=<?= $code ?>" 
           class="filter-tab <?= $country === $code ? 'active' : '' ?>"
           role="tab" aria-selected="<?= $country === $code ? 'true' : 'false' ?>">
          <span class="filter-tab-flag"><?= $data['flag'] ?></span>
          <span><?= htmlspecialchars($data['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($country)): ?>
      <?php if (empty($results)): ?>
        <div class="empty-state">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="2" y1="12" x2="22" y2="12"></line>
            <line x1="12" y1="2" x2="12" y2="22"></line>
          </svg>
          <h3>No results found</h3>
          <p>No animated shows from this country yet</p>
        </div>
      <?php else: ?>
        <div class="content-grid" data-stagger>
          <?php foreach ($results as $item): ?>
            <article class="content-card">
              <a href="show.php?id=<?= $item['tmdb_id'] ?>" class="card-link">
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
                  <span class="card-type">TV Show</span>
                </div>
                <div class="card-info">
                  <h3 class="card-title"><?= htmlspecialchars($item['title']) ?></h3>
                  <div class="card-meta">
                    <?php if (!empty($item['first_air_date'])): ?>
                      <span class="year"><?= htmlspecialchars(substr($item['first_air_date'], 0, 4)) ?></span>
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
                        18 => 'Drama', 12 => 'Adventure', 14 => 'Fantasy', 9648 => 'Mystery'
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
          <nav class="pagination" aria-label="Country pagination">
            <?php if ($pageNum > 1): ?>
              <a href="?country=<?= urlencode($country) ?>&page=<?= $pageNum - 1 ?>" class="page-btn prev">← Prev</a>
            <?php endif; ?>
            
            <?php 
            $start = max(1, $pageNum - 2);
            $end = min($totalPages, $pageNum + 2);
            if ($start > 1) echo '<span class="page-ellipsis">…</span>';
            for ($i = $start; $i <= $end; $i++): ?>
              <a href="?country=<?= urlencode($country) ?>&page=<?= $i ?>" class="page-btn <?= $i === $pageNum ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($end < $totalPages) echo '<span class="page-ellipsis">…</span>'; ?>
            
            <?php if ($pageNum < $totalPages): ?>
              <a href="?country=<?= urlencode($country) ?>&page=<?= $pageNum + 1 ?>" class="page-btn next">Next →</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    <?php else: ?>
      <!-- Countries landing page -->
      <div class="country-grid" data-stagger>
        <?php foreach ($countries as $code => $data): ?>
          <a href="countries.php?country=<?= $code ?>" class="country-card">
            <div class="country-card-flag"><?= $data['flag'] ?></div>
            <span class="country-card-name"><?= htmlspecialchars($data['name']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>