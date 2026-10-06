<?php
/**
 * index.php — Home page with cinematic intro and real API data sections.
 */

$page = 'home';
$pageTitle = 'Cartoon Universe — A Hand-Drawn World';
$fullIntro = true;

require_once __DIR__ . '/includes/header.php';

// Fetch real data for homepage sections
$trendingShows = $tmdb->getTrendingTv('week', 1);
$popularShows = $tmdb->getPopularTv(1);
$topRatedShows = $tmdb->getTopRatedTv(1);
$airingToday = $tmdb->getTvAiringToday(1);
$popularMovies = $tmdb->getPopularMovies(1);
$topRatedMovies = $tmdb->getTopRatedMovies(1);

// Get featured show for hero (first trending show with backdrop)
$featuredShow = null;
if ($trendingShows && !empty($trendingShows['results'])) {
    foreach ($trendingShows['results'] as $show) {
        if (!empty($show['backdrop_path'])) {
            $featuredShow = $tmdb->formatTvShow($show);
            break;
        }
    }
}
?>

<main class="hero" id="hero">
  <?php if ($featuredShow): ?>
    <div class="hero-backdrop" style="background-image: url('<?= htmlspecialchars($featuredShow['backdrop_url']) ?>')"></div>
    <div class="hero-gradient"></div>
    <div class="hero-content">
      <div class="hero-poster">
        <?php if (!empty($featuredShow['poster_url'])): ?>
          <img src="<?= htmlspecialchars($featuredShow['poster_url']) ?>" alt="" loading="eager">
        <?php endif; ?>
      </div>
      <div class="hero-info">
        <div class="hero-badges">
          <?php if (!empty($featuredShow['first_air_date'])): ?>
            <span class="hero-badge year"><?= htmlspecialchars(substr($featuredShow['first_air_date'], 0, 4)) ?></span>
          <?php endif; ?>
          <?php if (!empty($featuredShow['vote_average'])): ?>
            <span class="hero-badge rating">★ <?= number_format($featuredShow['vote_average'], 1) ?></span>
          <?php endif; ?>
          <?php if (!empty($featuredShow['origin_country'])): ?>
            <span class="hero-badge country"><?= htmlspecialchars(strtoupper($featuredShow['origin_country'][0])) ?></span>
          <?php endif; ?>
        </div>
        <h1 class="hero-title"><?= htmlspecialchars($featuredShow['title']) ?></h1>
        <p class="hero-overview"><?= htmlspecialchars($featuredShow['overview'] ?? 'No description available.') ?></p>
        <div class="hero-genres">
          <?php 
          $genreMap = [
              16 => 'Animation', 10751 => 'Family', 10759 => 'Action & Adventure',
              10762 => 'Kids', 10765 => 'Sci-Fi & Fantasy', 35 => 'Comedy',
              18 => 'Drama', 12 => 'Adventure', 14 => 'Fantasy', 9648 => 'Mystery',
              28 => 'Action', 10759 => 'Action & Adventure'
          ];
          foreach (array_slice($featuredShow['genre_ids'] ?? [], 0, 4) as $gid): ?>
            <?php if (isset($genreMap[$gid])): ?>
              <span class="genre-tag"><?= $genreMap[$gid] ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <div class="hero-actions">
          <a href="<?= $featuredShow['media_type'] === 'tv' ? 'show.php?id=' : 'movie.php?id=' ?><?= $featuredShow['tmdb_id'] ?>" class="btn-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            <span>Watch Now</span>
          </a>
          <?php if ($currentUser): ?>
            <button class="btn-pill favorite-btn" data-tmdb-id="<?= $featuredShow['tmdb_id'] ?>" data-media-type="<?= $featuredShow['media_type'] ?>">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              <span>Add to Favorites</span>
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="hero-fallback">
      <h1 class="title-wrap"><span id="bigTitle">
          <span class="t-line">CARTOON</span>
          <span class="t-line">UNIVERSE</span>
        </span></h1>
      <p class="tagline"><?= htmlspecialchars($DATA['site']['tagline']) ?></p>
      <a class="ticket" href="episodes.php">
        <span class="ticket-dot"></span>
        <span><?= htmlspecialchars($DATA['site']['episode_tag']) ?></span>
      </a>
    </div>
  <?php endif; ?>
</main>

<div id="mainContent" class="main-content">
  <?php if ($trendingShows && !empty($trendingShows['results'])): ?>
    <?= renderSection('Trending This Week', array_slice($trendingShows['results'], 0, 12), true, 'shows.php?sort=trending') ?>
  <?php endif; ?>

  <?php if ($popularShows && !empty($popularShows['results'])): ?>
    <?= renderSection('Popular Shows', array_slice($popularShows['results'], 0, 12), true, 'shows.php') ?>
  <?php endif; ?>

  <?php if ($airingToday && !empty($airingToday['results'])): ?>
    <?= renderSection('Airing Today', array_slice($airingToday['results'], 0, 12), true, 'episodes.php') ?>
  <?php endif; ?>

  <?php if ($popularMovies && !empty($popularMovies['results'])): ?>
    <?= renderSection('Popular Movies', array_slice($popularMovies['results'], 0, 12), false, 'categories.php?genre=Movies') ?>
  <?php endif; ?>

  <?php if ($topRatedShows && !empty($topRatedShows['results'])): ?>
    <?= renderSection('Top Rated', array_slice($topRatedShows['results'], 0, 12), true, 'shows.php?sort=top-rated') ?>
  <?php endif; ?>

  <?php if ($topRatedMovies && !empty($topRatedMovies['results'])): ?>
    <?= renderSection('Top Rated Movies', array_slice($topRatedMovies['results'], 0, 12), false, 'categories.php?genre=Top+Rated+Movies') ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>