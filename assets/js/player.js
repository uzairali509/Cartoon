// Player JavaScript for Cartoon Universe
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('videoPlayer');
    const container = document.getElementById('videoPlayerContainer');
    const unavailable = document.getElementById('playerUnavailable');
    const controls = document.getElementById('playerControls');
    
    const playPauseBtn = document.getElementById('playPauseBtn');
    const playIcon = playPauseBtn?.querySelector('.play-icon');
    const pauseIcon = playPauseBtn?.querySelector('.pause-icon');
    
    const rewindBtn = document.getElementById('rewindBtn');
    const forwardBtn = document.getElementById('forwardBtn');
    const muteBtn = document.getElementById('muteBtn');
    const volumeIcon = muteBtn?.querySelector('.volume-icon');
    const muteIcon = muteBtn?.querySelector('.mute-icon');
    const volumeSlider = document.getElementById('volumeSlider');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    const fullscreenIcon = fullscreenBtn?.querySelector('.fullscreen-icon');
    const exitFullscreenIcon = fullscreenBtn?.querySelector('.exit-fullscreen-icon');
    
    const progressFill = document.getElementById('progressFill');
    const progressHandle = document.getElementById('progressHandle');
    const timeDisplay = document.getElementById('timeDisplay');
    const volumeSliderEl = document.getElementById('volumeSlider');
    
    const rewindBtnEl = document.getElementById('rewindBtn');
    const forwardBtnEl = document.getElementById('forwardBtn');
    const muteBtnEl = document.getElementById('muteBtn');
    const fullscreenBtnEl = document.getElementById('fullscreenBtn');
    
    const playerUnavailable = document.getElementById('playerUnavailable');
    const videoPlayer = document.getElementById('videoPlayer');
    const countdownEl = document.getElementById('countdown');
    const sidebar = document.getElementById('episodeSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const nextUpCard = document.querySelector('.next-up-player-card');
    
    // State
    let isPlaying = false;
    let isMuted = false;
    let isFullscreen = false;
    let controlsTimeout;
    let countdownInterval;
    let autoPlayTimer;
    
    // Show unavailable message since no licensed video source
    function showUnavailable() {
        if (video) video.style.display = 'none';
        if (unavailable) unavailable.style.display = 'flex';
        if (controls) controls.style.display = 'none';
    }
    
    function formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        if (hrs > 0) {
            return `${hrs}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
    
    function updateProgress() {
        if (!video) return;
        const percent = video.duration ? (video.currentTime / video.duration) * 100 : 0;
        if (progressFill) progressFill.style.width = percent + '%';
        if (progressHandle) progressHandle.style.left = percent + '%';
        if (timeDisplay) {
            timeDisplay.textContent = `${formatTime(video.currentTime)} / ${formatTime(video.duration)}`;
        }
    }
    
    function togglePlay() {
        if (!video) return;
        if (video.paused) {
            video.play().catch(() => showUnavailable());
        } else {
            video.pause();
        }
    }
    
    function toggleMute() {
        if (!video) return;
        video.muted = !video.muted;
        isMuted = video.muted;
        updateVolumeUI();
    }
    
    function updateVolumeUI() {
        if (!video) return;
        if (volumeIcon) volumeIcon.style.display = video.muted || video.volume === 0 ? 'none' : 'block';
        if (muteIcon) muteIcon.style.display = video.muted || video.volume === 0 ? 'block' : 'none';
        if (volumeSliderEl) volumeSliderEl.value = video.volume;
    }
    
    function setVolume(value) {
        if (!video) return;
        video.volume = Math.max(0, Math.min(1, value));
        isMuted = video.volume === 0;
        video.muted = isMuted;
        updateVolumeUI();
    }
    
    function toggleFullscreen() {
        if (!container) return;
        if (!document.fullscreenElement) {
            container.requestFullscreen().catch(console.error);
        } else {
            document.exitFullscreen();
        }
    }
    
    function updateFullscreenUI() {
        isFullscreen = !!document.fullscreenElement;
        if (fullscreenIcon) fullscreenIcon.style.display = isFullscreen ? 'none' : 'block';
        if (exitFullscreenIcon) exitFullscreenIcon.style.display = isFullscreen ? 'block' : 'none';
    }
    
    function rewind10() {
        if (!video) return;
        video.currentTime = Math.max(0, video.currentTime - 10);
    }
    
    function forward10() {
        if (!video) return;
        video.currentTime = Math.min(video.duration, video.currentTime + 10);
    }
    
    // Seek on progress bar click
    function seek(e) {
        if (!video || !progressFill) return;
        const rect = progressFill.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        video.currentTime = Math.max(0, Math.min(video.duration, video.duration * percent));
    }
    
    // Volume slider click
    function setVolumeFromSlider(e) {
        if (!video || !volumeSliderEl) return;
        const rect = volumeSliderEl.getBoundingClientRect();
        const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        setVolume(percent);
    }
    
    // Controls visibility
    function showControls() {
        if (controls) controls.classList.add('visible');
        clearTimeout(controlsTimeout);
        controlsTimeout = setTimeout(hideControls, 3000);
    }
    
    function hideControls() {
        if (controls) controls.classList.remove('visible');
    }
    
    // Next episode countdown
    function startCountdown() {
        let seconds = 15;
        if (countdownEl) countdownEl.textContent = seconds;
        
        countdownInterval = setInterval(() => {
            seconds--;
            if (countdownEl) countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                if (nextUpCard) nextUpCard.click();
            }
        }, 1000);
    }
    
    // Auto-play next episode
    function setupAutoPlay() {
        if (!video) return;
        video.addEventListener('ended', () => {
            if (nextUpCard) {
                nextUpCard.click();
            }
        });
    }
    
    // Sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            if (sidebar) sidebar.classList.toggle('open');
        });
    }
    
    // Close sidebar on mobile when clicking episode
    document.querySelectorAll('.sidebar-episode').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 760 && sidebar) {
                sidebar.classList.remove('open');
            }
        });
    });
    
    // Countdown for next episode
    if (nextUpCard) {
        startCountdown();
        nextUpCard.addEventListener('click', (e) => {
            e.preventDefault();
            clearInterval(countdownInterval);
            window.location.href = nextUpCard.href;
        });
    }
    
    // Event Listeners
    if (video) {
        video.addEventListener('timeupdate', updateProgress);
        video.addEventListener('loadedmetadata', updateProgress);
        video.addEventListener('play', () => { isPlaying = true; updatePlayPauseUI(); });
        video.addEventListener('pause', () => { isPlaying = false; updatePlayPauseUI(); });
        video.addEventListener('volumechange', updateVolumeUI);
        video.addEventListener('ended', () => {
            isPlaying = false;
            updatePlayPauseUI();
        });
        video.addEventListener('error', showUnavailable);
    }
    
    function updatePlayPauseUI() {
        if (playIcon) playIcon.style.display = isPlaying ? 'none' : 'block';
        if (pauseIcon) pauseIcon.style.display = isPlaying ? 'block' : 'none';
    }
    
    if (playPauseBtn) playPauseBtn.addEventListener('click', togglePlay);
    if (video) video.addEventListener('click', togglePlay);
    
    if (rewindBtnEl) rewindBtnEl.addEventListener('click', rewind10);
    if (forwardBtnEl) forwardBtnEl.addEventListener('click', forward10);
    
    if (muteBtnEl) muteBtnEl.addEventListener('click', toggleMute);
    if (volumeSliderEl) {
        volumeSliderEl.addEventListener('input', (e) => setVolume(e.target.value));
        volumeSliderEl.addEventListener('change', (e) => setVolume(e.target.value));
    }
    
    if (fullscreenBtnEl) fullscreenBtnEl.addEventListener('click', toggleFullscreen);
    document.addEventListener('fullscreenchange', updateFullscreenUI);
    
    if (progressFill) {
        progressFill.addEventListener('click', seek);
        progressFill.addEventListener('mousedown', (e) => {
            seek(e);
            document.addEventListener('mousemove', seek);
            document.addEventListener('mouseup', () => document.removeEventListener('mousemove', seek), { once: true });
        });
    }
    
    if (volumeSliderEl) {
        volumeSliderEl.addEventListener('click', setVolumeFromSlider);
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        switch (e.code) {
            case 'Space':
            case 'KeyK':
                e.preventDefault();
                togglePlay();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                rewind10();
                break;
            case 'ArrowRight':
                e.preventDefault();
                forward10();
                break;
            case 'ArrowUp':
                e.preventDefault();
                setVolume(Math.min(1, (video?.volume || 0) + 0.1));
                break;
            case 'ArrowDown':
                e.preventDefault();
                setVolume(Math.max(0, (video?.volume || 0) - 0.1));
                break;
            case 'KeyM':
                e.preventDefault();
                toggleMute();
                break;
            case 'KeyF':
                e.preventDefault();
                toggleFullscreen();
                break;
            case 'Escape':
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
                break;
        }
    });
    
    // Mouse movement shows controls
    if (container) {
        container.addEventListener('mousemove', showControls);
        container.addEventListener('mouseleave', hideControls);
    }
    
    // Initialize
    showUnavailable();
    setupAutoPlay();
    updateVolumeUI();
    updatePlayPauseUI();
    updateFullscreenUI();
    
    // Countdown for next episode
    if (nextUpCard) {
        startCountdown();
    }
    
    // Sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            if (sidebar) sidebar.classList.toggle('open');
        });
    }
    
    // Close sidebar on episode click (mobile)
    document.querySelectorAll('.sidebar-episode').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 760 && sidebar) {
                sidebar.classList.remove('open');
            }
        });
    });
    
    // Episode sidebar links - update watch history
    document.querySelectorAll('.sidebar-episode').forEach(link => {
        link.addEventListener('click', function(e) {
            // Could add watch history tracking here
        });
    });
});