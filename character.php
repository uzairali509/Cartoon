<?php
/**
 * character.php — Character detail page
 */

$page = 'characters';
$pageTitle = 'Character — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

$personId = (int)($_GET['id'] ?? 0);
if (!$personId) {
    header('Location: characters.php');
    exit;
}

// Fetch person/character details from TMDB
$person = $tmdb->getPersonDetails($personId);
if (!$person) {
    include __DIR__ . '/includes/header.php';
    ?>
    <main class="page-wrap">
      <section class="page-card">
        <div class="empty-state" style="padding: 60px 20px; text-align: center;">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 20px; opacity: 0.5;">
            <circle cx="12" cy="8" r="4"></circle>
            <path d="M12 14c-4.42 0-8 3.58-8 8v2h16v-2c0-4.42-3.58-8-8-8z"></path>
          </svg>
          <h3 class="page-title">Character Not Found</h3>
          <p style="color: var(--text-secondary);">The character you're looking for doesn't exist.</p>
          <a href="characters.php" class="btn-primary" style="margin-top: 20px; display: inline-block;">← Back to Characters</a>
        </div>
      </section>
    </main>
    <?php include __DIR__ . '/includes/footer.php';
    exit;
}

$personData = $tmdb->formatPerson($person);
$credits = $tmdb->getPersonCredits($personId);

$pageTitle = $personData['name'] . ' — Cartoon Universe';

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card character-detail">
    <!-- Character Header -->
    <div class="character-header">
      <div class="character-image">
        <?php if (!empty($personData['profile_url'])): ?>
          <img src="<?= htmlspecialchars($personData['profile_url']) ?>" alt="" loading="eager">
        <?php else: ?>
          <div class="character-placeholder">
            <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="8" r="4"></circle>
              <path d="M12 14c-4.42 0-8 3.58-8 8v2h16v-2c0-4.42-3.58-8-8-8z"></path>
            </svg>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="character-main-info">
        <h1 class="character-name"><?= htmlspecialchars($personData['name']) ?></h1>
        
        <div class="character-meta">
          <?php if (!empty($personData['known_for_department'])): ?>
            <span class="meta-item">
              <span class="meta-label">Known For:</span>
              <span><?= htmlspecialchars($personData['known_for_department']) ?></span>
            </span>
          <?php endif; ?>
          <?php if (!empty($personData['gender'])): ?>
            <span class="meta-item">
              <span class="meta-label">Gender:</span>
              <span><?= $personData['gender'] === 1 ? 'Female' : ($personData['gender'] === 2 ? 'Male' : 'Non-binary') ?></span>
            </span>
          <?php endif; ?>
          <?php if (!empty($personData['profile_url'])): ?>
            <span class="meta-item">
              <span class="meta-label">Profile:</span>
              <span>Available</span>
            </span>
          <?php endif; ?>
        </div>
        
        <?php if (!empty($personData['known_for'])): ?>
          <div class="character-known-for">
            <h3>Known For</h3>
            <div class="known-for-grid">
              <?php foreach (array_slice($personData['known_for'], 0, 4) as $work): ?>
                <?php 
                  $formatted = $work['media_type'] === 'tv' ? $tmdb->formatTvShow($work) : $tmdb->formatMovie($work);
                ?>
                <article class="known-for-card">
                  <a href="<?= $work['media_type'] === 'tv' ? 'show.php?id=' . $work['id'] : 'movie.php?id=' . $work['id'] ?>" class="known-for-link">
                    <div class="known-for-poster">
                      <?php if (!empty($formatted['poster_url'])): ?>
                        <img src="<?= htmlspecialchars($formatted['poster_url']) ?>" alt="" loading="lazy">
                      <?php else: ?>
                        <div class="poster-placeholder"></div>
                      <?php endif; ?>
                    </div>
                    <div class="known-for-info">
                      <h4><?= htmlspecialchars($formatted['title']) ?></h4>
                      <span class="known-for-role"><?= htmlspecialchars($work['character'] ?? $work['name'] ?? '') ?></span>
                    </div>
                  </a>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Filmography/TV Credits -->
    <?php if ($credits && (!empty($credits['cast']) || !empty($credits['crew']))): ?>
      <div class="character-section">
        <header class="section-header">
          <h2 class="section-title">Filmography</h2>
        </header>
        
        <?php if (!empty($credits['cast'])): ?>
          <h3 class="subsection-title">As Cast</h3>
          <div class="credits-carousel" data-carousel>
            <div class="carousel-track">
              <?php foreach (array_slice($credits['cast'], 0, 12) as $credit): ?>
                <article class="credit-card">
                  <a href="show.php?id=<?= $credit['id'] ?>" class="credit-link">
                    <div class="credit-poster">
                      <?php if (!empty($credit['poster_path'])): ?>
                        <img src="<?= $tmdb->getImageUrl($credit['poster_path']) ?>" alt="" loading="lazy">
                      <?php else: ?>
                        <div class="poster-placeholder"></div>
                      <?php endif; ?>
                    </div>
                    <div class="credit-info">
                      <h4><?= htmlspecialchars($credit['name'] ?? $credit['title'] ?? '') ?></h4>
                      <p class="credit-role">as <?= htmlspecialchars($credit['character'] ?? 'Unknown') ?></p>
                      <?php if (!empty($credit['episode_count'])): ?>
                        <span class="credit-episodes"><?= (int)$credit['episode_count'] ?> episodes</span>
                      <?php endif; ?>
                    </div>
                  </a>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($credits['crew'])): ?>
          <h3 class="subsection-title">Crew</h3>
          <div class="credits-carousel" data-carousel>
            <div class="carousel-track">
              <?php foreach (array_slice($credits['crew'], 0, 8) as $credit): ?>
                <article class="credit-card">
                  <a href="show.php?id=<?= $credit['id'] ?>" class="credit-link">
                    <div class="credit-poster">
                      <?php if (!empty($credit['poster_path'])): ?>
                        <img src="<?= $tmdb->getImageUrl($credit['poster_path']) ?>" alt="" loading="lazy">
                      <?php else: ?>
                        <div class="poster-placeholder"></div>
                      <?php endif; ?>
                    </div>
                    <div class="credit-info">
                      <h4><?= htmlspecialchars($credit['name'] ?? '') ?></h4>
                      <p class="credit-role"><?= htmlspecialchars($credit['job'] ?? '') ?></p>
                      <p class="credit-department"><?= htmlspecialchars($credit['department'] ?? '') ?></p>
                    </div>
                  </a>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>