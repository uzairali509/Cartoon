<?php
/**
 * favorites.php — User favorites page
 */

$page = 'favorites';
$pageTitle = 'My Favorites — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

// Require login
requireLogin();

$pdo = getConnection();
$currentUser = getCurrentUser();

$favorites = getFavorites($pdo, $currentUser['id']);
$tvFavorites = array_filter($favorites, fn($f) => $f['media_type'] === 'tv');
$movieFavorites = array_filter($favorites, fn($f) => $f['media_type'] === 'movie');

// Fetch full details for favorites from TMDB
$favoriteShows = [];
$favoriteMovies = [];

foreach ($tvFavorites as $fav) {
    $show = $tmdb->getTvDetails($fav['tmdb_id']);
    if ($show) {
        $favoriteShows[] = array_merge($tmdb->formatTvShow($show), ['media_type' => 'tv', 'fav_id' => $fav['id']]);
    }
}

foreach ($movieFavorites as $fav) {
    $movie = $tmdb->getMovieDetails($fav['tmdb_id']);
    if ($movie) {
        $favoriteMovies[] = array_merge($tmdb->formatMovie($movie), ['media_type' => 'movie', 'fav_id' => $fav['id']]);
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title">My Favorites</h1>
      <p class="page-sub">Your saved cartoons and animated shows</p>
    </header>

    <!-- Tab Navigation -->
    <div class="filter-tabs" role="tablist">
      <button class="filter-tab active" data-tab="shows" role="tab" aria-selected="true">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>
        <span>TV Shows (<?= count($favoriteShows) ?>)</span>
      </button>
      <button class="filter-tab" data-tab="movies" role="tab" aria-selected="false">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>
        <span>Movies (<?= count($favoriteMovies) ?>)</span>
      </button>
    </div>

    <!-- TV Shows Tab -->
    <div class="tab-panel active" id="tab-shows" role="tabpanel">
      <?php if (empty($favoriteShows)): ?>
        <div class="empty-state">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
          <h3>No favorite shows yet</h3>
          <p>Start adding shows to your favorites!</p>
          <a href="shows.php" class="btn-primary">Browse Shows</a>
        </div>
      <?php else: ?>
        <div class="content-grid" data-stagger>
          <?php foreach ($favoriteShows as $show): ?>
            <article class="content-card">
              <a href="show.php?id=<?= $show['tmdb_id'] ?>" class="card-link">
                <div class="card-poster">
                  <?php if (!empty($show['poster_url'])): ?>
                    <img src="<?= htmlspecialchars($show['poster_url']) ?>" alt="" loading="lazy">
                  <?php else: ?>
                    <div class="poster-placeholder">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
                        <circle cx="12" cy="12" r="4"></circle>
                      </svg>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="card-info">
                  <h3 class="card-title"><?= htmlspecialchars($show['title']) ?></h3>
                  <div class="card-meta">
                    <?php if (!empty($show['first_air_date'])): ?>
                      <span class="year"><?= htmlspecialchars(substr($show['first_air_date'], 0, 4)) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($show['vote_average'])): ?>
                      <span class="rating">★ <?= number_format($show['vote_average'], 1) ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($show['genre_ids'])): ?>
                    <div class="card-genres">
                      <?php 
                      $genreMap = [
                          16 => 'Animation', 10751 => 'Family', 10759 => 'Action & Adventure',
                          10762 => 'Kids', 10765 => 'Sci-Fi & Fantasy', 35 => 'Comedy',
                          18 => 'Drama', 12 => 'Adventure', 14 => 'Fantasy', 9648 => 'Mystery',
                          28 => 'Action', 10759 => 'Action & Adventure'
                      ];
                      foreach (array_slice($show['genre_ids'], 0, 3) as $gid): ?>
                        <?php if (isset($genreMap[$gid])): ?>
                          <span class="genre-badge"><?= $genreMap[$gid] ?></span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </a>
              <button class="remove-fav-btn" data-fav-id="<?= $show['fav_id'] ?>" data-media-type="tv" aria-label="Remove from favorites">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              </button>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Movies Tab -->
    <div class="tab-panel" id="tab-movies" role="tabpanel" hidden>
      <?php if (empty($favoriteMovies)): ?>
        <div class="empty-state">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
          <h3>No favorite movies yet</h3>
          <p>Start adding movies to your favorites!</p>
          <a href="categories.php?genre=Movies" class="btn-primary">Browse Movies</a>
        </div>
      <?php else: ?>
        <div class="content-grid" data-stagger>
          <?php foreach ($favoriteMovies as $movie): ?>
            <article class="content-card">
              <a href="movie.php?id=<?= $movie['tmdb_id'] ?>" class="card-link">
                <div class="card-poster">
                  <?php if (!empty($movie['poster_url'])): ?>
                    <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="" loading="lazy">
                  <?php else: ?>
                    <div class="poster-placeholder">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
                        <circle cx="12" cy="12" r="4"></circle>
                      </svg>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="card-info">
                  <h3 class="card-title"><?= htmlspecialchars($movie['title']) ?></h3>
                  <div class="card-meta">
                    <?php if (!empty($movie['release_date'])): ?>
                      <span class="year"><?= htmlspecialchars(substr($movie['release_date'], 0, 4)) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($movie['vote_average'])): ?>
                      <span class="rating">★ <?= number_format($movie['vote_average'], 1) ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($movie['genre_ids'])): ?>
                    <div class="card-genres">
                      <?php 
                      $genreMap = [
                          16 => 'Animation', 10751 => 'Family', 10759 => 'Action & Adventure',
                          10762 => 'Kids', 10765 => 'Sci-Fi & Fantasy', 35 => 'Comedy',
                          18 => 'Drama', 12 => 'Adventure', 14 => 'Fantasy', 9648 => 'Mystery',
                          28 => 'Action', 10759 => 'Action & Adventure'
                      ];
                      foreach (array_slice($movie['genre_ids'], 0, 3) as $gid): ?>
                        <?php if (isset($genreMap[$gid])): ?>
                          <span class="genre-badge"><?= $genreMap[$gid] ?></span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </a>
              <button class="remove-fav-btn" data-fav-id="<?= $movie['fav_id'] ?>" data-media-type="movie" aria-label="Remove from favorites">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              </button>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Tab switching
  const tabBtns = document.querySelectorAll('.filter-tab');
  const tabPanels = document.querySelectorAll('.tab-panel');
  
  tabBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const tab = this.dataset.tab;
      
      tabBtns.forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      this.classList.add('active');
      this.setAttribute('aria-selected', 'true');
      
      tabPanels.forEach(panel => {
        panel.hidden = true;
        panel.classList.remove('active');
      });
      
      const panel = document.getElementById('tab-' + tab);
      if (panel) {
        panel.hidden = false;
        panel.classList.add('active');
      }
    });
  });

  // Remove favorite buttons
  document.querySelectorAll('.remove-fav-btn').forEach(btn => {
    btn.addEventListener('click', async function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const favId = this.dataset.favId;
      const mediaType = this.dataset.mediaType;
      
      if (confirm('Remove from favorites?')) {
        try {
          const response = await fetch('api/favorites.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove', fav_id: favId, media_type: mediaType })
          });
          
          const result = await response.json();
          if (result.success) {
            // Remove card from DOM
            const card = this.closest('.content-card');
            if (card) card.remove();
            
            // Update count
            const tabBtn = document.querySelector('.filter-tab[data-tab="' + (mediaType === 'tv' ? 'shows' : 'movies') + '"]');
            if (tabBtn) {
              const span = tabBtn.querySelector('span');
              if (span) {
                const count = parseInt(span.textContent.match(/\((\d+)\)/)?.[1] || '0') - 1;
                span.textContent = mediaType === 'tv' ? 'TV Shows (' + count + ')' : 'Movies (' + count + ')';
              }
            }
            
            // Check if empty
            const grid = document.querySelector('#tab-' + (mediaType === 'tv' ? 'shows' : 'movies') + ' .content-grid');
            if (grid && grid.children.length === 0) {
              location.reload();
            }
          }
        } catch (e) {
          console.error('Error removing favorite:', e);
          alert('Failed to remove favorite');
        }
      }
    });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>