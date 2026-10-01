<?php
/**
 * dashboard.php — User dashboard page with real data
 */

require_once __DIR__ . '/includes/config.php';

$page = 'dashboard';
$pageTitle = 'Dashboard — Cartoon Universe';
$fullIntro = false;

// Require login
requireLogin();

$currentUser = getCurrentUser();
$pdo = getConnection();

// Fetch user's favorites
$favorites = getFavorites($pdo, $currentUser['id']);
$tvFavorites = array_filter($favorites, fn($f) => $f['media_type'] === 'tv');
$movieFavorites = array_filter($favorites, fn($f) => $f['media_type'] === 'movie');

// Fetch watch history
$watchHistory = getWatchHistory($pdo, $currentUser['id'], 5);

// Fetch liked content
$userLikes = getUserLikes($pdo, $currentUser['id']);
$likedShows = array_filter($userLikes, fn($l) => $l['media_type'] === 'tv');
$likedMovies = array_filter($userLikes, fn($l) => $l['media_type'] === 'movie');

// Fetch full details for favorites
$favoriteShows = [];
$favoriteMovies = [];

foreach ($tvFavorites as $fav) {
    $show = $tmdb->getTvDetails($fav['tmdb_id']);
    if ($show) {
        $favoriteShows[] = array_merge($tmdb->formatTvShow($show), ['fav_id' => $fav['id']]);
    }
}

foreach ($movieFavorites as $fav) {
    $movie = $tmdb->getMovieDetails($fav['tmdb_id']);
    if ($movie) {
        $favoriteMovies[] = array_merge($tmdb->formatMovie($movie), ['fav_id' => $fav['id']]);
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title">Dashboard</h1>
      <p class="page-sub">Welcome back, <?= htmlspecialchars($currentUser['name']) ?></p>
    </header>

    <!-- Profile Header -->
    <div class="profile-header">
      <div class="profile-avatar">
        <?php if ($currentUser['avatar_path']): ?>
          <img src="<?= e($currentUser['avatar_path']) ?>" alt="<?= e($currentUser['name']) ?>">
        <?php else: ?>
          <img src="assets/svg/<?= e(['fox','bear','cat','bunny','star','rocket','cactus','icecream'][($currentUser['id'] % 8)]) ?>.svg" alt="">
        <?php endif; ?>
      </div>
      <div class="profile-info">
        <h2><?= e($currentUser['name']) ?></h2>
        <p class="profile-email"><?= e($currentUser['email']) ?></p>
        <div class="profile-meta">
          <span>Member since <?= e(date('F Y', strtotime($currentUser['created_at']))) ?></span>
        </div>
      </div>
      <div class="profile-actions">
        <a href="profile.php" class="btn-pill">Edit Profile</a>
        <a href="logout.php" class="btn-pill">Log Out</a>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <article class="stat-card">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>
        </div>
        <div class="stat-value"><?= count($watchHistory) ?></div>
        <div class="stat-label">Episodes Watched</div>
      </article>
      <article class="stat-card">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 9 8.26 12 2"></polygon></svg>
        </div>
        <div class="stat-value">
          <?php 
          $highScore = 0;
          if (isset($_COOKIE['cu_starpop_best'])) {
              $highScore = (int)$_COOKIE['cu_starpop_best'];
          }
          echo $highScore;
          ?>
        </div>
        <div class="stat-label">High Score</div>
      </article>
      <article class="stat-card">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        </div>
        <div class="stat-value"><?= count($favorites) ?></div>
        <div class="stat-label">Favorites</div>
      </article>
      <article class="stat-card">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        </div>
        <div class="stat-value"><?= count($userLikes) ?></div>
        <div class="stat-label">Likes Given</div>
      </article>
    </div>

    <!-- Continue Watching -->
    <?php if (!empty($watchHistory)): ?>
      <section class="dashboard-section">
        <header class="section-header">
          <h2 class="section-title">Continue Watching</h2>
        </header>
        <div class="content-grid" data-stagger>
          <?php foreach ($watchHistory as $item): ?>
            <article class="content-card">
              <a href="<?= $item['media_type'] === 'tv' ? 'show.php?id=' . $item['tmdb_id'] : 'movie.php?id=' . $item['tmdb_id'] ?>" class="card-link">
                <div class="card-poster">
                  <?php if (!empty($item['poster_path'])): ?>
                    <img src="<?= $tmdb->getImageUrl($item['poster_path']) ?>" alt="" loading="lazy">
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
                      $genreMap = [
                          16 => 'Animation', 10751 => 'Family', 10759 => 'Action & Adventure',
                          10762 => 'Kids', 10765 => 'Sci-Fi & Fantasy', 35 => 'Comedy',
                          18 => 'Drama', 12 => 'Adventure', 14 => 'Fantasy', 9648 => 'Mystery',
                          28 => 'Action', 10759 => 'Action & Adventure'
                      ];
                      foreach (array_slice($item['genre_ids'], 0, 3) as $gid): ?>
                        <?php if (isset($genreMap[$gid])): ?>
                          <span class="genre-badge"><?= $genreMap[$gid] ?></span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Favorites -->
    <?php if (!empty($favoriteShows) || !empty($favoriteMovies)): ?>
      <section class="dashboard-section">
        <header class="section-header">
          <h2 class="section-title">Your Favorites</h2>
        </header>
        <div class="content-grid" data-stagger>
          <?php foreach (array_merge($favoriteShows, $favoriteMovies) as $item): ?>
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
                  </div>
                </div>
                <button class="remove-fav-btn" data-fav-id="<?= $item['fav_id'] ?>" data-media-type="<?= $item['media_type'] ?>" aria-label="Remove from favorites">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </button>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Quick Actions -->
    <section class="dashboard-section">
      <header class="section-header">
        <h2 class="section-title">Quick Actions</h2>
      </header>
      <div class="action-grid">
        <a href="episodes.php" class="action-card">
          <div class="action-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>
          </div>
          <span>Latest Episodes</span>
        </a>
        <a href="characters.php" class="action-card">
          <div class="action-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"></circle><path d="M12 14c-4.42 0-8 3.58-8 8v2h16v-2c0-4.42-3.58-8-8-8z"></path></svg>
          </div>
          <span>Characters</span>
        </a>
        <a href="shows.php" class="action-card">
          <div class="action-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>
          </div>
          <span>All Shows</span>
        </a>
        <a href="games.php" class="action-card">
          <div class="action-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 9 8.26 12 2"></polygon></svg>
          </div>
          <span>Play Star Pop</span>
        </a>
        <a href="favorites.php" class="action-card">
          <div class="action-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          </div>
          <span>My Favorites</span>
        </a>
        <a href="about.php" class="action-card">
          <div class="action-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </div>
          <span>About</span>
        </a>
      </div>
    </section>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>