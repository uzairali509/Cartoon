<?php
/**
 * about.php — About page with professional design
 */

$page = 'about';
$pageTitle = 'About — Cartoon Universe';
$fullIntro = false;

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title">About Cartoon Universe</h1>
      <p class="page-sub">A professional cartoon discovery platform</p>
    </header>

    <div class="about-content" data-stagger>
      <section class="about-section">
        <h2 class="about-heading">Our Mission</h2>
        <p class="about-lead">Cartoon Universe is a professional cartoon discovery platform built for animation enthusiasts of all ages. We believe that great animation transcends age — it inspires, entertains, and connects people across generations.</p>
        <p>Our platform aggregates data from trusted entertainment metadata sources to provide comprehensive information about animated TV shows, movies, characters, and the talented people who bring them to life.</        </section>

      <section class="about-section">
        <h2 class="about-heading">What We Offer</h2>
        <div class="features-grid">
          <article class="feature-card">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>
            </div>
            <h3>Discover Content</h3>
            <p>Browse thousands of animated shows and movies with detailed information, ratings, and recommendations.
          </article>
          <article class="feature-card">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <h3>Track Progress</h3>
            <p>Keep track of what you've watched, save favorites, and get personalized recommendations.
          </article>
          <article class="feature-card">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></path><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <h3>Community</h3>
            <p>Connect with fellow animation fans, share favorites, and discuss your favorite shows.
          </article>
        </div>
      </section>

      <section class="about-section">
        <h2 class="about-heading">Data Sources</h2>
        <p>Our data is powered by <a href="https://www.themoviedb.org/" target="_blank" rel="noopener">The Movie Database (TMDB)</a>, a community-built movie and TV database. We're grateful for their comprehensive API that makes this platform possible.
        <p>All poster images, backdrops, and metadata are sourced from TMDB and used in accordance with their <a href="https://www.themoviedb.org/documentation/api/terms-of-use" target="_blank" rel="noopener">Terms of Use</a>.</p>
      </section>

      <section class="about-section">
        <h2 class="about-heading">Legal & Privacy</h2>
        <p>Cartoon Universe is a fan-made discovery platform. We do not host any video content. All trademarks, characters, and images belong to their respective owners.
        <p>For privacy inquiries or data requests, please contact us through our <a href="mailto:privacy@cartoonuniverse.example">privacy email</a>.</p>
      </section>

      <section class="about-section newsletter-section">
        <h2 class="about-heading">Stay Updated</h2>
        <p>Join our newsletter for the latest animated releases, recommendations, and platform updates.</p>
        <form class="newsletter-form" method="post" action="about.php">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfField()) ?>">
          <div class="form-group">
            <label for="newsletter-name">Your Name</label>
            <input type="text" id="newsletter-name" name="name" maxlength="40" placeholder="Your name" required>
          </div>
          <div class="form-group">
            <label for="newsletter-email">Email Address</label>
            <input type="email" id="newsletter-email" name="email" required placeholder="you@example.com">
          </div>
          <button type="submit" name="join_newsletter" class="btn-primary">SUBSCRIBE</button>
        </form>
        <p class="join-note">We respect your privacy. Unsubscribe anytime.</p>
      </section>

      <p class="about-sign">— The Cartoon Universe Team</p>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>