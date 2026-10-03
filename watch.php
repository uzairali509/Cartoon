<?php
/**
 * watch.php — Video player page
 */
require_once __DIR__ . '/includes/config.php';

$videoId = (int)($_GET['id'] ?? 0);
$showId = (int)($_GET['show_id'] ?? 0);
$seasonNumber = (int)($_GET['season'] ?? 0);
$episodeNumber = (int)($_GET['episode'] ?? 0);
$mediaType = $_GET['type'] ?? 'tv';

$pdo = getConnection();

if ($videoId) {
    // Direct video ID
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ? AND status = 'ready'");
    $stmt->execute([$videoId]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($showId) {
    // Find primary video for show/episode
    $sql = "SELECT * FROM videos WHERE tmdb_id = ? AND media_type = ? AND status = 'ready'";
    $params = [$showId, $mediaType];
    if ($seasonNumber) { $sql .= " AND season_number = ?"; $params[] = $seasonNumber; }
    if ($episodeNumber) { $sql .= " AND episode_number = ?"; $params[] = $episodeNumber; }
    $sql .= " ORDER BY is_primary DESC, quality DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$video) {
    include __DIR__ . '/includes/header.php';
    ?>
    <main class="page-wrap">
      <section class="page-card">
        <div class="empty-state" style="padding: 60px 20px; text-align: center;">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 20px; opacity: 0.5;">
            <polygon points="23 7 16 12 23 17 23 7"></polygon>
            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
          </svg>
          <h3 class="page-title">Video Not Found</h3>
          <p style="color: var(--text-secondary);">The video you're looking for doesn't exist or is still processing.</p>
          <a href="index.php" class="btn-primary" style="margin-top: 20px; display: inline-block;">← Back Home</a>
        </div>
      </section>
    </main>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Increment view count
incrementVideoViews($pdo, $video['id']);

// Get show/movie details from TMDB
$showData = null;
if ($video['media_type'] === 'tv') {
    $show = $tmdb->getTvDetails($video['tmdb_id']);
    if ($show) $showData = $tmdb->formatTvShow($show);
} else {
    $movie = $tmdb->getMovieDetails($video['tmdb_id']);
    if ($movie) $showData = $tmdb->formatMovie($movie);
}

$pageTitle = $video['title'] . ' — ' . ($showData['title'] ?? 'Cartoon Universe');
$fullIntro = false;

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap watch-page">
  <section class="page-card watch-container">
    <!-- Video Player -->
    <div class="video-player-wrapper">
      <div class="video-player" id="videoPlayer">
        <video 
          id="mainVideo" 
          class="video-element"
          controls 
          preload="metadata"
          playsinline
          poster="<?= htmlspecialchars($video['thumbnail_path'] ?? '') ?>"
          crossorigin="anonymous"
        >
          <source src="<?= htmlspecialchars($video['video_path']) ?>" type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <div class="video-loading" id="videoLoading">
          <div class="spinner"></div>
          <span>Loading...</span>
        </div>
      </div>
      
      <!-- Video Info Overlay -->
      <div class="video-info-overlay">
        <h1 class="video-title"><?= htmlspecialchars($video['title']) ?></h1>
        <?php if ($showData): ?>
          <div class="video-show-info">
            <a href="<?= $video['media_type'] === 'tv' ? 'show.php?id=' : 'movie.php?id=' ?><?= $video['tmdb_id'] ?>" class="show-link">
              <?php if (!empty($showData['poster_url'])): ?>
                <img src="<?= htmlspecialchars($showData['poster_url']) ?>" alt="" class="show-poster-sm" loading="lazy">
              <?php endif; ?>
              <span class="show-name"><?= htmlspecialchars($showData['title']) ?></span>
            </a>
            <?php if (!empty($video['season_number'])): ?>
              <span class="episode-badge">S<?= str_pad($video['season_number'], 2, '0', STR_PAD_LEFT) ?> E<?= str_pad($video['episode_number'], 2, '0', STR_PAD_LEFT) ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($video['description'])): ?>
          <p class="video-description"><?= htmlspecialchars($video['description']) ?></p>
        <?php endif; ?>
        <div class="video-meta">
          <span class="quality-badge"><?= htmlspecialchars($video['quality']) ?></span>
          <span class="duration"><?= gmdate($video['duration'] >= 3600 ? 'H:i:s' : 'i:s', $video['duration']) ?></span>
          <span class="views"><?= number_format($video['views']) ?> views</span>
        </div>
      </div>
    </div>
    
    <!-- Next Episode / Related Videos -->
    <?php if ($video['media_type'] === 'tv' && !empty($video['season_number']) && !empty($video['episode_number'])): ?>
      <?php
      $nextEpisodeNumber = $video['episode_number'] + 1;
      $stmt = $pdo->prepare("
          SELECT * FROM videos 
          WHERE tmdb_id = ? AND media_type = ? AND season_number = ? AND episode_number = ? 
          AND status = 'ready' AND is_primary = 1
          ORDER BY quality DESC LIMIT 1
      ");
      $stmt->execute([$video['tmdb_id'], $video['media_type'], $video['season_number'], $nextEpisodeNumber]);
      $nextVideo = $stmt->fetch(PDO::FETCH_ASSOC);
      ?>
      <?php if ($nextVideo): ?>
        <div class="next-episode-card">
          <a href="watch.php?id=<?= $nextVideo['id'] ?>" class="next-episode-link">
            <div class="next-episode-thumb">
              <?php if (!empty($nextVideo['thumbnail_path'])): ?>
                <img src="<?= htmlspecialchars($nextVideo['thumbnail_path']) ?>" alt="" loading="lazy">
              <?php else: ?>
                <div class="video-placeholder">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polygon points="23 7 16 12 23 17 23 7"></polygon>
                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                  </svg>
                </div>
              <?php endif; ?>
              <span class="next-badge">NEXT</span>
            </div>
            <div class="next-episode-info">
              <span class="next-label">Next Episode</span>
              <h3><?= htmlspecialchars($nextVideo['title']) ?></h3>
              <span class="next-meta">S<?= str_pad($nextVideo['season_number'], 2, '0', STR_PAD_LEFT) ?>E<?= str_pad($nextVideo['episode_number'], 2, '0', STR_PAD_LEFT) ?> • <?= gmdate($nextVideo['duration'] >= 3600 ? 'H:i:s' : 'i:s', $nextVideo['duration']) ?></span>
            </div>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--accent-primary);">
              <path d="M5 12h14M12 5l7 7-7 7"></path>
            </svg>
          </a>
        </div>
      <?php endif; ?>
    <?php endif; ?>
    
    <!-- Video Actions -->
    <div class="video-actions">
      <?php if ($currentUser): ?>
        <button class="btn-pill favorite-btn" data-video-id="<?= $videoId ?>" data-media-type="<?= $video['media_type'] ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          <span>ADD TO FAVORITES</span>
        </button>
        <button class="btn-pill share-btn" data-video-id="<?= $videoId ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
          <span>SHARE</span>
        </button>
      <?php endif; ?>
      <a href="<?= $video['media_type'] === 'tv' ? 'show.php?id=' : 'movie.php?id=' ?><?= $video['tmdb_id'] ?>" class="btn-pill">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
        <span>BACK TO SHOW</span>
      </a>
    </div>
    
    <!-- Comments Section -->
    <?php if ($currentUser): ?>
    <div class="video-comments">
      <h2 class="section-title">Comments</h2>
      <form class="comment-form" id="commentForm">
        <input type="hidden" name="video_id" value="<?= $videoId ?>">
        <textarea name="comment" placeholder="Add a comment..." required rows="2"></textarea>
        <button class="btn-primary" type="submit">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"></path><path d="M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
          <span>POST</span>
        </button>
      </form>
      <div class="comments-list" id="commentsList">
        <p class="no-comments">No comments yet. Be the first to comment!</p>
      </div>
    </div>
    <?php endif; ?>
  </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('mainVideo');
    const loading = document.getElementById('videoLoading');
    
    // Hide loading when video can play
    video.addEventListener('canplay', function() {
        loading.style.display = 'none';
    });
    
    video.addEventListener('waiting', function() {
        loading.style.display = 'flex';
    });
    
    video.addEventListener('playing', function() {
        loading.style.display = 'none';
    });
    
    // Save watch progress
    let progressTimer;
    video.addEventListener('timeupdate', function() {
        clearTimeout(progressTimer);
        progressTimer = setTimeout(function() {
            if (video.duration > 0) {
                const progress = video.currentTime / video.duration;
                fetch('api/save_progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        video_id: <?= $videoId ?>,
                        progress: progress,
                        current_time: video.currentTime
                    })
                }).catch(console.error);
            }
        }, 5000); // Save every 5 seconds
    });
    
    // Comment form
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('api/add_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.reset();
                    loadComments();
                }
            });
        });
    }
    
    function loadComments() {
        fetch('api/get_comments.php?video_id=<?= $videoId ?>')
            .then(r => r.json())
            .then(data => {
                const list = document.getElementById('commentsList');
                if (data.comments && data.comments.length > 0) {
                    list.innerHTML = data.comments.map(c => `
                        <div class="comment">
                            <div class="comment-avatar">
                                <?php if (!empty($currentUser['avatar_path'])): ?>
                                    <img src="<?= e($currentUser['avatar_path']) ?>" alt="">
                                <?php else: ?>
                                    <div class="avatar-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"></circle><path d="M12 14c-4.42 0-8 3.58-8 8v2h16v-2c0-4.42-3.58-8-8-8z"></path></svg></div>
                                <?php endif; ?>
                            </div>
                            <div class="comment-body">
                                <div class="comment-header">
                                    <span class="comment-author"><?= htmlspecialchars($currentUser['name']) ?></span>
                                    <span class="comment-time">Just now</span>
                                </div>
                                <p class="comment-text">${c.comment}</p>
                            </div>
                        </div>
                    `).join('');
                }
            });
    }
    
    loadComments();
});
</script>

<style>
.watch-page {
    padding: calc(var(--header-height) + 20px) 16px 40px;
}
.watch-container {
    max-width: 1200px;
    margin: 0 auto;
}
.video-player-wrapper {
    position: relative;
    background: #000;
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 24px;
}
.video-player {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
}
.video-element {
    width: 100%;
    height: 100%;
    display: block;
}
.video-loading {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: rgba(0,0,0,0.8);
    color: var(--text-primary);
    z-index: 10;
}
.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--border-default);
    border-top-color: var(--accent-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.video-info-overlay {
    padding: 24px;
}
.video-title {
    font-family: var(--font-display);
    font-size: clamp(1.5rem, 3vw, 2rem);
    margin-bottom: 12px;
}
.video-show-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.show-link {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-secondary);
    text-decoration: none;
    transition: color var(--transition-fast);
}
.show-link:hover { color: var(--accent-primary); }
.show-poster-sm { width: 40px; height: 60px; object-fit: cover; border-radius: 6px; }
.show-name { font-weight: 600; }
.episode-badge {
    background: var(--accent-primary);
    color: #fff;
    padding: 4px 10px;
    border-radius: var(--radius-full);
    font-size: .75rem;
    font-weight: 700;
}
.video-description {
    color: var(--text-secondary);
    margin-bottom: 16px;
    line-height: 1.6;
}
.video-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: .85rem;
    color: var(--text-muted);
}
.quality-badge {
    background: var(--accent-primary-light);
    color: var(--accent-primary);
    padding: 4px 10px;
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: .75rem;
}
.next-episode-card {
    margin-top: 24px;
}
.next-episode-link {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    text-decoration: none;
    transition: var(--transition-fast);
}
.next-episode-link:hover {
    border-color: var(--accent-primary);
    transform: translateX(4px);
}
.next-episode-thumb {
    position: relative;
    width: 160px;
    aspect-ratio: 16/9;
    border-radius: var(--radius-md);
    overflow: hidden;
    flex-shrink: 0;
}
.next-episode-thumb img { width: 100%; height: 100%; object-fit: cover; }
.next-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--accent-primary);
    color: #fff;
    padding: 2px 8px;
    border-radius: var(--radius-full);
    font-size: .65rem;
    font-weight: 700;
}
.next-episode-info { flex: 1; min-width: 0; }
.next-label { font-size: .7rem; color: var(--accent-primary); font-weight: 600; text-transform: uppercase; }
.next-episode-info h3 { font-family: var(--font-display); font-size: 1.1rem; margin: 4px 0; color: var(--text-primary); }
.next-meta { font-size: .8rem; color: var(--text-muted); }
.video-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 32px;
}
.video-comments {
    border-top: 1px solid var(--border-subtle);
    padding-top: 24px;
}
.comment-form {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}
.comment-form textarea {
    flex: 1;
    background: var(--bg-secondary);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-md);
    padding: 12px;
    color: var(--text-primary);
    font-family: var(--font-sans);
    resize: vertical;
    min-height: 80px;
}
.comment-form textarea:focus {
    outline: none;
    border-color: var(--accent-primary);
}
.comment-form .btn-primary { height: fit-content; padding: 12px 20px; }
.comments-list { display: flex; flex-direction: column; gap: 16px; }
.comment {
    display: flex;
    gap: 12px;
    padding: 16px;
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-subtle);
}
.comment-avatar { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; flex-shrink: 0; }
.comment-avatar img { width: 100%; height: 100%; object-fit: cover; }
.avatar-placeholder { width: 100%; height: 100%; background: var(--accent-primary-light); color: var(--accent-primary); display: flex; align-items: center; justify-content: center; }
.comment-body { flex: 1; min-width: 0; }
.comment-header { display: flex; gap: 12px; margin-bottom: 4px; font-size: .85rem; }
.comment-author { font-weight: 600; color: var(--text-primary); }
.comment-time { color: var(--text-muted); }
.comment-text { color: var(--text-secondary); line-height: 1.5; }
.no-comments { text-align: center; color: var(--text-muted); padding: 32px; }
.upload-form { display: flex; flex-direction: column; gap: 20px; }
.form-row { display: flex; gap: 16px; flex-wrap: wrap; }
.form-row > label { flex: 1; min-width: 200px; }
.form-row > label:only-child { flex: 1 1 100%; }
.upload-form label { display: flex; flex-direction: column; gap: 8px; font-size: .7rem; font-weight: 600; letter-spacing: .05em; color: var(--text-muted); text-transform: uppercase; }
.upload-form input, .upload-form select, .upload-form textarea {
    font-family: var(--font-sans);
    font-size: 1rem;
    padding: 14px 16px;
    color: var(--text-primary);
    background: var(--bg-secondary);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-md);
}
.upload-form input:focus, .upload-form select:focus, .upload-form textarea:focus {
    outline: none;
    border-color: var(--accent-primary);
    box-shadow: 0 0 0 4px var(--accent-primary-light);
}
.form-hint { font-size: .75rem; color: var(--text-muted); margin-top: 4px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: .85rem; color: var(--text-secondary); }
.videos-list { display: flex; flex-direction: column; gap: 16px; }
.video-item {
    display: flex;
    gap: 16px;
    padding: 16px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    transition: var(--transition-fast);
}
.video-item:hover { border-color: var(--accent-primary); }
.video-thumbnail { position: relative; width: 200px; aspect-ratio: 16/9; border-radius: var(--radius-md); overflow: hidden; flex-shrink: 0; }
.video-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
.video-placeholder { width: 100%; height: 100%; background: var(--bg-card); display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
.video-duration { position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.8); color: #fff; padding: 2px 6px; border-radius: 4px; font-size: .7rem; font-weight: 600; }
.video-quality { position: absolute; top: 8px; left: 8px; background: var(--accent-primary); color: #fff; padding: 2px 6px; border-radius: 4px; font-size: .65rem; font-weight: 700; }
.video-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
.video-title { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 8px; color: var(--text-primary); }
.video-meta { display: flex; flex-wrap: wrap; gap: 12px; font-size: .75rem; color: var(--text-muted); margin-bottom: 12px; }
@media (max-width: 768px) {
    .video-item { flex-direction: column; }
    .video-thumbnail { width: 100%; }
    .form-row { flex-direction: column; }
    .form-row > label { min-width: 0; }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>