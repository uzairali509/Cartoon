<?php
/**
 * index.php — Cartoon Universe Home Page
 * 
 * Cinematic intro animation followed by immersive home experience.
 * Uses GIF-based intro cards and premium scroll animations.
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

// Get random characters for floating elements
$characters = $DATA['characters'] ?? [];
shuffle($characters);
$floatChars = array_slice($characters, 0, 6);
?>

<main class="hero" id="hero">
  <div class="sky" aria-hidden="true">
    <div class="sun-wrap" aria-hidden="true">
      <div class="sun-halo"></div>
      <svg class="sun-svg" viewBox="0 0 200 200" aria-hidden="true">
        <circle cx="100" cy="100" r="90" fill="#FFD567" />
        <g stroke="#FFC84A" stroke-width="4" stroke-linecap="round">
          <line x1="100" y1="15" x2="100" y2="35" />
          <line x1="100" y1="165" x2="100" y2="185" />
          <line x1="15" y1="100" x2="35" y2="100" />
          <line x1="165" y1="100" x2="185" y2="100" />
          <line x1="38" y1="38" x2="55" y2="55" />
          <line x1="145" y1="145" x2="162" y2="162" />
          <line x1="38" y1="162" x2="55" y2="145" />
          <line x1="145" y1="55" x2="162" y2="38" />
        </g>
      </svg>
    </div>

    <div class="cloud c1" aria-hidden="true">
      <svg viewBox="0 0 200 100" aria-hidden="true">
        <path d="M30,50 Q10,50 10,70 Q10,90 50,90 Q80,90 80,50 Q80,20 50,20 Q20,20 20,50 Q20,80 50,80" fill="#FFFDF7" />
      </svg>
    </div>

    <div class="cloud c2" aria-hidden="true">
      <svg viewBox="0 0 200 100" aria-hidden="true">
        <path d="M30,50 Q10,50 10,70 Q10,90 50,90 Q80,90 80,50 Q80,20 50,20 Q20,20 20,50 Q20,80 50,80" fill="#FFFDF7" />
      </svg>
    </div>

    <div class="cloud c3" aria-hidden="true">
      <svg viewBox="0 0 200 100" aria-hidden="true">
        <path d="M30,50 Q10,50 10,70 Q10,90 50,90 Q80,90 80,50 Q80,20 50,20 Q20,20 20,50 Q20,80 50,80" fill="#FFFDF7" />
      </svg>
    </div>

    <div class="cloud c4" aria-hidden="true">
      <svg viewBox="0 0 200 100" aria-hidden="true">
        <path d="M30,50 Q10,50 10,70 Q10,90 50,90 Q80,90 80,50 Q80,20 50,20 Q20,20 20,50 Q20,80 50,80" fill="#FFFDF7" />
      </svg>
    </div>

    <svg class="hills" viewBox="0 0 1440 300" preserveAspectRatio="none" aria-hidden="true">
      <path d="M0,200 Q200,100 400,180 Q600,250 800,190 Q1000,130 1200,200 Q1300,240 1440,160 L1440,300 L0,300 Z" fill="var(--bg-secondary)" />
      <path d="M0,250 Q200,180 400,240 Q600,300 800,240 Q1000,180 1200,250 Q1300,290 1440,210 L1440,300 L0,300 Z" fill="var(--bg-card)" opacity="0.6" />
    </svg>

    <div class="floater f-fox" data-depth="0.15" aria-hidden="true">
      <div class="bob">
        <svg viewBox="0 0 100 100" aria-hidden="true">
          <circle cx="50" cy="50" r="45" fill="#FF6B35" />
          <ellipse cx="50" cy="70" rx="25" ry="20" fill="#FF8C5A" />
          <circle cx="35" cy="40" r="8" fill="#FFFDF7" />
          <circle cx="65" cy="40" r="8" fill="#FFFDF7" />
          <circle cx="35" cy="40" r="4" fill="#33261D" />
          <circle cx="65" cy="40" r="4" fill="#33261D" />
          <ellipse cx="50" cy="65" rx="12" ry="6" fill="#33261D" />
        </svg>
      </div>
    </div>

    <div class="floater f-bunny" data-depth="0.12" aria-hidden="true">
      <div class="bob">
        <svg viewBox="0 0 100 100" aria-hidden="true">
          <ellipse cx="50" cy="60" rx="28" ry="32" fill="#FFFDF7" />
          <ellipse cx="30" cy="25" rx="12" ry="22" fill="#FFFDF7" />
          <ellipse cx="70" cy="25" rx="12" ry="22" fill="#FFFDF7" />
          <ellipse cx="30" cy="25" rx="6" ry="14" fill="#FFB6C1" />
          <ellipse cx="70" cy="25" rx="6" ry="14" fill="#FFB6C1" />
          <circle cx="42" cy="55" r="4" fill="#33261D" />
          <circle cx="58" cy="55" r="4" fill="#33261D" />
          <ellipse cx="50" cy="72" rx="8" ry="4" fill="#33261D" />
        </svg>
      </div>
    </div>

    <div class="floater f-star" data-depth="0.08" aria-hidden="true">
      <div class="bob">
        <svg viewBox="0 0 100 100" aria-hidden="true">
          <path d="M50 10 L58 40 L90 42 L64 62 L72 94 L50 78 L28 94 L36 62 L10 42 L42 40 Z" fill="#FFD567" stroke="#33261D" stroke-width="2" />
          <circle cx="42" cy="50" r="3" fill="#33261D" />
          <circle cx="58" cy="50" r="3" fill="#33261D" />
          <path d="M42 60 Q50 65 58 60" stroke="#33261D" stroke-width="2" fill="none" stroke-linecap="round" />
        </svg>
      </div>
    </div>

    <div class="floater f-balloon" data-depth="0.18" aria-hidden="true">
      <div class="bob">
        <svg viewBox="0 0 100 100" aria-hidden="true">
          <path d="M50 10 Q35 25 35 50 Q35 75 50 90 Q65 75 65 50 Q65 25 50 10" fill="#FF6B35" stroke="#33261D" stroke-width="2" />
          <ellipse cx="50" cy="30" rx="8" ry="6" fill="#FFA07A" opacity="0.6" />
          <path d="M50 90 Q50 95 55 100" stroke="#33261D" stroke-width="2" fill="none" stroke-linecap="round" />
        </svg>
      </div>
    </div>

    <div id="particles" aria-hidden="true"></div>
  </div>

  <?php if ($featuredShow): ?>
    <div class="hero-backdrop" style="background-image: url('<?= htmlspecialchars($featuredShow['backdrop_url']) ?>')" aria-hidden="true"></div>
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
  <!-- Floating Character Cards -->
  <div class="float-chars" aria-hidden="true">
    <?php foreach ($floatChars as $i => $char): ?>
      <div class="float-char" style="--i: <?= $i ?>;" data-character="<?= htmlspecialchars($char['scene']) ?>">
        <div class="float-char-inner">
          <img src="assets/gifs/<?= htmlspecialchars($char['scene']) ?>.gif" alt="<?= htmlspecialchars($char['name']) ?>" loading="lazy"
               onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjAwIDI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjI1MCIgZmlsbD0iI0ZGOWM2MyIvPjwvc3ZnPg=='; this.style.opacity='0.5';">
          <span class="float-char-name"><?= htmlspecialchars($char['name']) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Content Sections with Scroll Animations -->
  <?php if ($trendingShows && !empty($trendingShows['results'])): ?>
    <section class="content-section reveal-section" data-reveal="fade-up">
      <header class="section-header">
        <h2 class="section-title">Trending This Week</h2>
        <a href="shows.php?sort=trending" class="view-all">View All →</a>
      </header>
      <div class="content-carousel" data-carousel>
        <div class="carousel-track">
          <?php foreach (array_slice($trendingShows['results'], 0, 12) as $show): ?>
            <?= renderShowCard($tmdb->formatTvShow($show), true) ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($popularShows && !empty($popularShows['results'])): ?>
    <section class="content-section reveal-section" data-reveal="fade-up">
      <header class="section-header">
        <h2 class="section-title">Popular Shows</h2>
        <a href="shows.php" class="view-all">View All →</a>
      </header>
      <div class="content-carousel" data-carousel>
        <div class="carousel-track">
          <?php foreach (array_slice($popularShows['results'], 0, 12) as $show): ?>
            <?= renderShowCard($tmdb->formatTvShow($show), true) ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($airingToday && !empty($airingToday['results'])): ?>
    <section class="content-section reveal-section" data-reveal="fade-up">
      <header class="section-header">
        <h2 class="section-title">Airing Today</h2>
        <a href="episodes.php" class="view-all">View All →</a>
      </header>
      <div class="content-carousel" data-carousel>
        <div class="carousel-track">
          <?php foreach (array_slice($airingToday['results'], 0, 12) as $show): ?>
            <?= renderShowCard($tmdb->formatTvShow($show), true) ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($popularMovies && !empty($popularMovies['results'])): ?>
    <section class="content-section reveal-section" data-reveal="fade-up">
      <header class="section-header">
        <h2 class="section-title">Popular Movies</h2>
        <a href="categories.php?genre=Movies" class="view-all">View All →</a>
      </header>
      <div class="content-carousel" data-carousel>
        <div class="carousel-track">
          <?php foreach (array_slice($popularMovies['results'], 0, 12) as $movie): ?>
            <?= renderShowCard($tmdb->formatMovie($movie), false) ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($topRatedShows && !empty($topRatedShows['results'])): ?>
    <section class="content-section reveal-section" data-reveal="fade-up">
      <header class="section-header">
        <h2 class="section-title">Top Rated Shows</h2>
        <a href="shows.php?sort=top-rated" class="view-all">View All →</a>
      </header>
      <div class="content-carousel" data-carousel>
        <div class="carousel-track">
          <?php foreach (array_slice($topRatedShows['results'], 0, 12) as $show): ?>
            <?= renderShowCard($tmdb->formatTvShow($show), true) ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($topRatedMovies && !empty($topRatedMovies['results'])): ?>
    <section class="content-section reveal-section" data-reveal="fade-up">
      <header class="section-header">
        <h2 class="section-title">Top Rated Movies</h2>
        <a href="categories.php?genre=Top+Rated+Movies" class="view-all">View All →</a>
      </header>
      <div class="content-carousel" data-carousel>
        <div class="carousel-track">
          <?php foreach (array_slice($topRatedMovies['results'], 0, 12) as $movie): ?>
            <?= renderShowCard($tmdb->formatMovie($movie), false) ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- Character Showcase -->
  <section class="content-section character-showcase reveal-section" data-reveal="fade-up">
    <header class="section-header">
      <h2 class="section-title">Meet the Characters</h2>
      <a href="characters.php" class="view-all">View All →</a>
    </header>
    <div class="character-grid">
      <?php foreach ($characters as $char): ?>
        <article class="character-card" data-character="<?= htmlspecialchars($char['scene']) ?>">
          <div class="character-card-inner">
            <img src="assets/gifs/<?= htmlspecialchars($char['scene']) ?>.gif" alt="<?= htmlspecialchars($char['name']) ?>" loading="lazy"
                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjAwIDI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjI1MCIgZmlsbD0iI0ZGOWM2MyIvPjwvc3ZnPg=='; this.style.opacity='0.5';">
            <div class="character-card-overlay">
              <h3 class="character-card-name"><?= htmlspecialchars($char['name']) ?></h3>
              <p class="character-card-role"><?= htmlspecialchars($char['role']) ?></p>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>