<?php
/**
 * upload_video.php — Handle video uploads for episodes/movies
 */
require_once __DIR__ . '/includes/config.php';

$page = 'upload';
$pageTitle = 'Upload Video — Cartoon Universe';
$fullIntro = false;

// Require login
requireLogin();

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_video'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $message_type = 'error';
    } else {
        $tmdb_id = (int)($_POST['tmdb_id'] ?? 0);
        $media_type = $_POST['media_type'] ?? 'tv';
        $season_number = !empty($_POST['season_number']) ? (int)$_POST['season_number'] : null;
        $episode_number = !empty($_POST['episode_number']) ? (int)$_POST['episode_number'] : null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $quality = $_POST['quality'] ?? '1080p';
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;
        
        // Validate
        if (!$tmdb_id) {
            $message = 'TMDB ID is required.';
            $message_type = 'error';
        } elseif (empty($title)) {
            $message = 'Title is required.';
            $message_type = 'error';
        } elseif (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] === UPLOAD_ERR_NO_FILE) {
            $message = 'Video file is required.';
            $message_type = 'error';
        } else {
            // Handle video upload
            $videoFile = $_FILES['video_file'];
            $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
            $maxSize = 500 * 1024 * 1024; // 500MB
            
            if ($videoFile['size'] > $maxSize) {
                $message = 'Video file too large. Max 500MB.';
                $message_type = 'error';
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $videoFile['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mimeType, $allowedVideoTypes)) {
                    $message = 'Invalid video format. Only MP4, WebM, OGG allowed.';
                    $message_type = 'error';
                } else {
                    // Upload video
                    $ext = 'mp4'; // Default to mp4
                    if ($mimeType === 'video/webm') $ext = 'webm';
                    elseif ($mimeType === 'video/ogg') $ext = 'ogg';
                    
                    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                    $uploadDir = __DIR__ . '/assets/uploads/videos/';
                    
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $destination = $uploadDir . $filename;
                    
                    if (move_uploaded_file($videoFile['tmp_name'], $destination)) {
                        // Handle thumbnail upload
                        $thumbnailPath = null;
                        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                            $thumbFile = $_FILES['thumbnail'];
                            $allowedThumbTypes = ['image/jpeg', 'image/png', 'image/webp'];
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $thumbMime = finfo_file($finfo, $thumbFile['tmp_name']);
                            finfo_close($finfo);
                            
                            if (in_array($thumbMime, $allowedThumbTypes)) {
                                $thumbExt = $thumbMime === 'image/png' ? 'png' : ($thumbMime === 'image/webp' ? 'webp' : 'jpg');
                                $thumbFilename = bin2hex(random_bytes(16)) . '_thumb.' . $thumbExt;
                                $thumbDestination = $uploadDir . $thumbFilename;
                                if (move_uploaded_file($thumbFile['tmp_name'], $thumbDestination)) {
                                    $thumbnailPath = 'assets/uploads/videos/' . $thumbFilename;
                                }
                            }
                        }
                        
                        // Get duration using ffprobe if available
                        $duration = 0;
                        $ffprobe = shell_exec('which ffprobe 2>/dev/null');
                        if ($ffprobe) {
                            $cmd = 'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . escapeshellarg($destination);
                            $output = shell_exec($cmd);
                            if ($output) {
                                $duration = (int)floatval(trim($output));
                            }
                        }
                        
                        // Save to database
                        $result = addVideo(getConnection(), [
                            'tmdb_id' => $tmdb_id,
                            'media_type' => $media_type,
                            'season_number' => $season_number,
                            'episode_number' => $episode_number,
                            'title' => $title,
                            'description' => $description,
                            'video_path' => 'assets/uploads/videos/' . $filename,
                            'thumbnail_path' => $thumbnailPath,
                            'duration' => $duration,
                            'quality' => $quality,
                            'is_primary' => $is_primary,
                            'uploaded_by' => $currentUser['id'],
                        ]);
                        
                        if ($result['success']) {
                            $message = 'Video uploaded successfully!';
                            $message_type = 'success';
                        } else {
                            $message = $result['message'];
                            $message_type = 'error';
                            // Clean up file
                            unlink($destination);
                            if ($thumbnailPath) unlink(__DIR__ . '/' . $thumbnailPath);
                        }
                    } else {
                        $message = 'Failed to save video file.';
                        $message_type = 'error';
                    }
                }
            }
        }
    }
}

// Get shows for dropdown
$trendingShows = $tmdb->getTrendingTv('week', 1);
$popularShows = $tmdb->getPopularTv(1);
$allShows = [];
if ($trendingShows && !empty($trendingShows['results'])) {
    foreach ($trendingShows['results'] as $show) {
        $allShows[] = $tmdb->formatTvShow($show);
    }
}
if ($popularShows && !empty($popularShows['results'])) {
    foreach ($popularShows['results'] as $show) {
        // Avoid duplicates
        $exists = false;
        foreach ($allShows as $existing) {
            if ($existing['id'] === $show['id']) { $exists = true; break; }
        }
        if (!$exists) $allShows[] = $tmdb->formatTvShow($show);
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title">Upload Video</h1>
      <p class="page-sub">Upload video files for shows and episodes</p>
    </header>
    
    <?php if (!empty($message)): ?>
      <div class="form-message <?= $message_type === 'success' ? 'success' : 'error' ?>">
        <span class="message-icon"><?= $message_type === 'success' ? '✓' : '!' ?></span>
        <span><?= $message ?></span>
      </div>
    <?php endif; ?>
    
    <form method="post" action="" class="upload-form" enctype="multipart/form-data">
      <?= csrfField() ?>
      
      <div class="form-row">
        <label>
          SELECT SHOW
          <select name="tmdb_id" id="tmdb_id" required>
            <option value="">Choose a show...</option>
            <?php foreach ($allShows as $show): ?>
              <option value="<?= $show['id'] ?>">
                <?= htmlspecialchars($show['title']) ?> (<?= htmlspecialchars(substr($show['first_air_date'] ?? '', 0, 4)) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        
        <label>
          MEDIA TYPE
          <select name="media_type" id="media_type" required>
            <option value="tv">TV Show</option>
            <option value="movie">Movie</option>
          </select>
        </label>
      </div>
      
      <div class="form-row" id="season_episode_fields">
        <label>
          SEASON NUMBER
          <input type="number" name="season_number" id="season_number" min="1" placeholder="1">
        </label>
        
        <label>
          EPISODE NUMBER
          <input type="number" name="episode_number" id="episode_number" min="1" placeholder="1">
        </label>
      </div>
      
      <label>
        VIDEO TITLE
        <input type="text" name="title" required maxlength="200" placeholder="Episode 1: The Beginning">
      </label>
      
      <label>
        DESCRIPTION (Optional)
        <textarea name="description" rows="3" placeholder="Brief description of this video..."></textarea>
      </label>
      
      <div class="form-row">
        <label>
          VIDEO FILE <span class="required">*</span>
          <input type="file" name="video_file" id="video_file" accept="video/mp4,video/webm,video/ogg" required>
          <small class="form-hint">MP4, WebM, or OGG (max 500MB)</small>
        </label>
        
        <label>
          THUMBNAIL (Optional)
          <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg,image/png,image/webp">
          <small class="form-hint">JPG, PNG, or WebP</small>
        </label>
      </div>
      
      <div class="form-row">
        <label>
          QUALITY
          <select name="quality" required>
            <option value="480p">480p</option>
            <option value="720p">720p</option>
            <option value="1080p" selected>1080p</option>
            <option value="4k">4K</option>
          </select>
        </label>
        
        <label class="checkbox-label">
          <input type="checkbox" name="is_primary" id="is_primary" value="1">
          <span>Set as primary video for this episode</span>
        </label>
      </div>
      
      <button class="btn-primary" type="submit" name="upload_video">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="17 8 12 3 7 8"></polyline>
          <line x1="12" y1="3" x2="12" y2="15"></line>
        </svg>
        <span>UPLOAD VIDEO</span>
      </button>
    </form>
    
    <hr style="margin: 32px 0; border-color: var(--border-subtle);">
    
    <h2 style="font-family: var(--font-display); margin-bottom: 16px;">Recently Uploaded Videos</h2>
    
    <?php 
    $videos = getAllVideos(getConnection(), 20);
    if (!empty($videos)): ?>
      <div class="videos-list" data-stagger>
        <?php foreach ($videos as $video): ?>
          <article class="video-item">
            <div class="video-thumbnail">
              <?php if (!empty($video['thumbnail_path'])): ?>
                <img src="<?= htmlspecialchars($video['thumbnail_path']) ?>" alt="" loading="lazy">
              <?php else: ?>
                <div class="video-placeholder">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polygon points="23 7 16 12 23 17 23 7"></polygon>
                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                  </svg>
                </div>
              <?php endif; ?>
              <span class="video-duration"><?= gmdate($video['duration'] >= 3600 ? 'H:i:s' : 'i:s', $video['duration']) ?></span>
              <span class="video-quality"><?= htmlspecialchars($video['quality']) ?></span>
            </div>
            <div class="video-info">
              <h4 class="video-title"><?= htmlspecialchars($video['title']) ?></h4>
              <div class="video-meta">
                <span><?= htmlspecialchars($video['media_type']) === 'tv' ? 'TV' : 'Movie' ?></span>
                <?php if (!empty($video['season_number'])): ?>
                  <span>S<?= str_pad($video['season_number'], 2, '0', STR_PAD_LEFT) ?>E<?= str_pad($video['episode_number'], 2, '0', STR_PAD_LEFT) ?></span>
                <?php endif; ?>
                <span><?= number_format($video['views']) ?> views</span>
                <span><?= htmlspecialchars($video['uploader_name'] ?? 'Unknown') ?></span>
              </div>
              <a href="watch.php?id=<?= $video['id'] ?>" class="btn-pill" style="padding: 8px 16px; font-size: .85rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                <span>WATCH</span>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px; opacity: 0.5;">
          <polygon points="23 7 16 12 23 17 23 7"></polygon>
          <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
        </svg>
        <h3>No videos uploaded yet</h3>
        <p>Be the first to upload a video!</p>
      </div>
    <?php endif; ?>
  </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mediaType = document.getElementById('media_type');
    const seasonEpisodeFields = document.getElementById('season_episode_fields');
    
    function toggleSeasonEpisode() {
        if (mediaType.value === 'tv') {
            seasonEpisodeFields.style.display = 'flex';
            document.getElementById('season_number').required = true;
            document.getElementById('episode_number').required = true;
        } else {
            seasonEpisodeFields.style.display = 'none';
            document.getElementById('season_number').required = false;
            document.getElementById('episode_number').required = false;
        }
    }
    
    mediaType.addEventListener('change', toggleSeasonEpisode);
    toggleSeasonEpisode();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>