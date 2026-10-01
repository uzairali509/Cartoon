<?php
/**
 * characters.php — Characters page with real TMDB data
 */

$page = 'characters';
$pageTitle = 'Characters — Cartoon Universe';
$fullIntro = false;

require_once __DIR__ . '/includes/config.php';

// Check if TMDB is configured
if (!$tmdb->isConfigured()) {
    $characters = [];
    $apiError = 'TMDB API key not configured. Please add your TMDB API key to the .env file.';
} else {
    // Fetch popular people known for animation/voice acting
    $popularPeople = $tmdb->getPopularPeople(1);
    $characters = [];

    if ($popularPeople && !empty($popularPeople['results'])) {
    foreach ($popularPeople['results'] as $person) {
        // Filter for people known for animation/voice acting
        $knownFor = $person['known_for'] ?? [];
        $isAnimation = false;
        foreach ($knownFor as $work) {
            if (in_array(16, $work['genre_ids'] ?? []) || in_array(10751, $work['genre_ids'] ?? []) || in_array(10762, $work['genre_ids'] ?? [])) {
                $isAnimation = true;
                break;
            }
        }
        if ($isAnimation || in_array($person['known_for_department'] ?? '', ['Acting', 'Voice Acting'])) {
            $characters[] = $tmdb->formatPerson($person);
        }
    }
}

    // If not enough, add more from search
    if (count($characters) < 12) {
        $searchResults = $tmdb->searchPerson('voice actor', 1);
        if ($searchResults && !empty($searchResults['results'])) {
            foreach ($searchResults['results'] as $person) {
                if (count($characters) >= 12) break;
                $characters[] = $tmdb->formatPerson($person);
            }
        }
    }

    // Fallback to some known voice actors if still not enough
    $fallbackIds = [287, 12835, 13240, 16265, 31, 24218, 11364, 6384, 1813, 51329]; // Known voice actors
    foreach ($fallbackIds as $id) {
        if (count($characters) >= 12) break;
        $person = $tmdb->getPersonDetails($id);
        if ($person) {
            $characters[] = $tmdb->formatPerson($person);
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title">Characters</h1>
      <p class="page-sub">Discover the voices behind your favorite animated characters</p>
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
    <?php elseif (empty($characters)): ?>
      <div class="empty-state">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="8" r="4"></circle>
          <path d="M12 14c-4.42 0-8 3.58-8 8v2h16v-2c0-4.42-3.58-8-8-8z"></path>
        </svg>
        <h3>No characters found</h3>
        <p>Unable to load character data at the moment</p>
      </div>
    <?php else: ?>
      <div class="content-grid" data-stagger>
        <?php foreach ($characters as $character): ?>
          <article class="content-card">
            <a href="character.php?id=<?= $character['tmdb_id'] ?>" class="card-link">
              <div class="card-poster">
                <?php if (!empty($character['profile_url'])): ?>
                  <img src="<?= htmlspecialchars($character['profile_url']) ?>" alt="" loading="lazy">
                <?php else: ?>
                  <div class="poster-placeholder">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <circle cx="12" cy="8" r="4"></circle>
                      <path d="M12 14c-4.42 0-8 3.58-8 8v2h16v-2c0-4.42-3.58-8-8-8z"></path>
                    </svg>
                  </div>
                <?php endif; ?>
                <span class="card-type">Actor</span>
              </div>
              <div class="card-info">
                <h3 class="card-title"><?= htmlspecialchars($character['name']) ?></h3>
                <div class="card-meta">
                  <span class="year"><?= htmlspecialchars($character['known_for_department'] ?? 'Voice Acting') ?></span>
                  <span class="rating">Popularity: <?= number_format($character['popularity'] ?? 0, 1) ?></span>
                </div>
                <?php if (!empty($character['known_for'])): ?>
                  <div class="card-genres">
                    <?php 
                    foreach (array_slice($character['known_for'], 0, 2) as $work): ?>
                      <span class="genre-badge"><?= htmlspecialchars($work['title'] ?? $work['name'] ?? '') ?> (<?= $work['media_type'] === 'tv' ? 'TV' : 'Movie' ?>)</span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
              <?php if ($currentUser): ?>
                <button class="favorite-btn" data-tmdb-id="<?= $character['tmdb_id'] ?>" data-media-type="person" aria-label="Add to favorites">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </button>
              <?php endif; ?>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>