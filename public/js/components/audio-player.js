// public/js/components/audio-player.js
document.addEventListener('DOMContentLoaded', function() {
    // Audio player variables
    let audioPlayer = null;
    let playPauseBtn = null;
    let progressBar = null;
    let currentTimeElement = null;
    let durationElement = null;
    let volumeBtn = null;
    let volumeSlider = null;
    let speedSelector = null;
    let minimizeToggle = null;
    let audioContainer = null;
    let audioChapterTitle = null;
    let isAudioInitialized = false;
    let isPlaying = false;
    let currentChapterId = null;
    let currentAudioPath = null;
    let audioMuted = false;
    let lastVolume = 1;
    let playbackSpeed = 1;
    let floatingPlayBtn = null;
    
    // Public methods for audio player (accessible to other scripts)
    window.ChapterAudio = {
        initialize,
        loadChapterAudio,
        play,
        pause,
        stop,
        isAvailable,
        togglePlay,
        seekTo,
        getCurrentTime,
        getDuration,
        isCurrentlyPlaying,
        isAudioLoaded,
        getCurrentChapterId
    };
    
    // Initialize the audio player
    function initialize() {
        if (isAudioInitialized) return;
        
        createAudioPlayerUI();
        setupEventListeners();
        
        isAudioInitialized = true;
    }
    
    // Create audio player UI
    function createAudioPlayerUI() {
        // Create audio player container if it doesn't exist
        if (!document.getElementById('chapterAudioPlayer')) {
            // Create floating play button (shown when audio player is minimized)
            floatingPlayBtn = document.createElement('div');
            floatingPlayBtn.className = 'floating-audio-play hidden';
            floatingPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
            document.body.appendChild(floatingPlayBtn);
            
            // Create audio player container
            audioContainer = document.createElement('div');
            audioContainer.id = 'chapterAudioPlayer';
            audioContainer.className = 'chapter-audio-player hidden';
            
            // Create audio element
            audioPlayer = document.createElement('audio');
            audioPlayer.id = 'chapterAudio';
            audioPlayer.preload = 'metadata';
            
            // Create player structure
            audioContainer.innerHTML = `
                <div class="audio-player-header" id="audioPlayerHeader">
                    <div class="audio-chapter-info">
                        <h4 class="audio-chapter-title" id="audioChapterTitle">Chapter Title</h4>
                    </div>
                    <button class="audio-toggle-minimize" id="audioMinimizeToggle">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="audio-controls">
                    <div class="audio-playback-controls">
                        <button class="audio-btn rewind-btn" title="Rewind 10 seconds">
                            <i class="fas fa-undo-alt"></i>
                        </button>
                        <button class="audio-btn play-pause" id="audioPlayPause" title="Play/Pause">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="audio-btn forward-btn" title="Forward 10 seconds">
                            <i class="fas fa-redo-alt"></i>
                        </button>
                    </div>
                    <div class="audio-progress-container">
                        <input type="range" class="audio-progress" id="audioProgress" min="0" max="100" value="0" step="0.1">
                        <div class="audio-time-info">
                            <span id="currentTime">0:00</span>
                            <span id="duration">0:00</span>
                        </div>
                    </div>
                    <div class="audio-additional-controls">
                        <select class="speed-selector" id="audioSpeed" title="Playback Speed">
                            <option value="0.75">0.75x</option>
                            <option value="1" selected>1x</option>
                            <option value="1.25">1.25x</option>
                            <option value="1.5">1.5x</option>
                            <option value="2">2x</option>
                        </select>
                        <div class="volume-container" id="audioVolumeContainer">
                            <button class="audio-btn volume-btn" id="audioVolume" title="Volume">
                                <i class="fas fa-volume-up"></i>
                            </button>
                            <input type="range" class="volume-slider" id="volumeSlider" min="0" max="1" step="0.01" value="1">
                        </div>
                    </div>
                </div>
            `;
            
            // Append the audio element to the container
            audioContainer.appendChild(audioPlayer);
            
            // Append the container to the body
            document.body.appendChild(audioContainer);
            
            // Cache DOM elements
            playPauseBtn = document.getElementById('audioPlayPause');
            progressBar = document.getElementById('audioProgress');
            currentTimeElement = document.getElementById('currentTime');
            durationElement = document.getElementById('duration');
            volumeBtn = document.getElementById('audioVolume');
            volumeSlider = document.getElementById('volumeSlider');
            speedSelector = document.getElementById('audioSpeed');
            minimizeToggle = document.getElementById('audioMinimizeToggle');
            audioChapterTitle = document.getElementById('audioChapterTitle');
        }
    }
    
    // Set up event listeners for audio player
    function setupEventListeners() {
        if (!audioPlayer) return;
        
        // Audio element events
        audioPlayer.addEventListener('loadedmetadata', onAudioLoaded);
        audioPlayer.addEventListener('timeupdate', updateProgress);
        audioPlayer.addEventListener('ended', onAudioEnded);
        audioPlayer.addEventListener('play', updatePlayPauseIcon);
        audioPlayer.addEventListener('pause', updatePlayPauseIcon);
        audioPlayer.addEventListener('waiting', showBuffering);
        audioPlayer.addEventListener('playing', hideBuffering);
        
        // Player control events
        playPauseBtn.addEventListener('click', togglePlay);
        progressBar.addEventListener('input', seekToPosition);
        volumeBtn.addEventListener('click', toggleMute);
        volumeSlider.addEventListener('input', changeVolume);
        speedSelector.addEventListener('change', changeSpeed);
        
        // Rewind and forward buttons
        document.querySelector('.rewind-btn').addEventListener('click', () => seekRelative(-10));
        document.querySelector('.forward-btn').addEventListener('click', () => seekRelative(10));
        
        // Minimize toggle
        minimizeToggle.addEventListener('click', toggleMinimize);
        
        // Header click to toggle minimize
        document.getElementById('audioPlayerHeader').addEventListener('click', function(e) {
            if (e.target !== minimizeToggle && !minimizeToggle.contains(e.target)) {
                toggleMinimize();
            }
        });
        
        // Floating play button click
        floatingPlayBtn.addEventListener('click', () => {
            if (audioContainer.classList.contains('minimized')) {
                toggleMinimize();
            }
            togglePlay();
        });
        
        // Volume container expansion for mobile
        document.getElementById('audioVolumeContainer').addEventListener('click', function(e) {
            if (window.innerWidth <= 767 && e.target === volumeBtn) {
                this.classList.toggle('expanded');
                e.stopPropagation(); // Prevent this from toggling minimize
            }
        });
        
        // Click outside volume container to collapse it
        document.addEventListener('click', function(e) {
            const volumeContainer = document.getElementById('audioVolumeContainer');
            if (volumeContainer && volumeContainer.classList.contains('expanded') && !volumeContainer.contains(e.target)) {
                volumeContainer.classList.remove('expanded');
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Only if audio is loaded
            if (!isAudioLoaded()) return;
            
            switch(e.key) {
                case ' ': // Space
                    if (document.activeElement.tagName !== 'BUTTON' && 
                        document.activeElement.tagName !== 'INPUT' && 
                        document.activeElement.tagName !== 'TEXTAREA' && 
                        document.activeElement.tagName !== 'SELECT') {
                        e.preventDefault();
                        togglePlay();
                    }
                    break;
                case 'ArrowLeft': // Left arrow
                    if (!isInputFocused()) {
                        seekRelative(-5);
                    }
                    break;
                case 'ArrowRight': // Right arrow
                    if (!isInputFocused()) {
                        seekRelative(5);
                    }
                    break;
                case 'm': // Mute/unmute
                    if (!isInputFocused()) {
                        toggleMute();
                    }
                    break;
            }
        });
    }
    
    // Check if an input element is focused
    function isInputFocused() {
        const activeElement = document.activeElement;
        return activeElement.tagName === 'INPUT' || 
               activeElement.tagName === 'TEXTAREA' || 
               activeElement.tagName === 'SELECT';
    }
    
    // Event handler when audio is loaded
    function onAudioLoaded() {
        if (!audioPlayer) return;
        
        // Update duration display
        const duration = audioPlayer.duration;
        durationElement.textContent = formatTime(duration);
        
        // Set initial progress max value
        progressBar.max = duration;
        
        // Show audio player
        audioContainer.classList.remove('hidden');
        
        // Update floating play button
        floatingPlayBtn.classList.remove('hidden');
        
        // Auto-play if it was playing before
        if (isPlaying) {
            audioPlayer.play().catch(err => {
                console.error('Auto-play failed:', err);
                isPlaying = false;
                updatePlayPauseIcon();
            });
        }
    }
    
    // Update progress bar and time display
    function updateProgress() {
        if (!audioPlayer || audioPlayer.paused) return;
        
        const currentTime = audioPlayer.currentTime;
        const duration = audioPlayer.duration;
        
        // Update progress bar
        progressBar.value = currentTime;
        
        // Update time display
        currentTimeElement.textContent = formatTime(currentTime);
        
        // Save progress to session storage every 5 seconds
        if (Math.floor(currentTime) % 5 === 0 && currentChapterId) {
            saveAudioProgress(currentChapterId, currentTime);
        }
    }
    
    // Event handler when audio playback ends
    function onAudioEnded() {
        isPlaying = false;
        updatePlayPauseIcon();
        
        // Reset progress
        progressBar.value = 0;
        currentTimeElement.textContent = '0:00';
        
        // Option: Auto-play next chapter
        // loadNextChapter();
    }
    
    // Show buffering indicator
    function showBuffering() {
        playPauseBtn.querySelector('i').classList.add('audio-loading');
    }
    
    // Hide buffering indicator
    function hideBuffering() {
        playPauseBtn.querySelector('i').classList.remove('audio-loading');
    }
    
    // Update play/pause button icon
    function updatePlayPauseIcon() {
        if (!playPauseBtn) return;
        
        const icon = playPauseBtn.querySelector('i');
        
        if (audioPlayer && !audioPlayer.paused) {
            icon.className = 'fas fa-pause';
            floatingPlayBtn.innerHTML = '<i class="fas fa-pause"></i>';
            isPlaying = true;
        } else {
            icon.className = 'fas fa-play';
            floatingPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
            isPlaying = false;
        }
    }
    
    // Toggle play/pause
    function togglePlay() {
        if (!audioPlayer) return;
        
        if (audioPlayer.paused) {
            audioPlayer.play().catch(err => {
                console.error('Play failed:', err);
            });
        } else {
            audioPlayer.pause();
        }
        
        isPlaying = !audioPlayer.paused;
        updatePlayPauseIcon();
    }
    
    // Seek to position in audio
    function seekToPosition() {
        if (!audioPlayer) return;
        
        const seekTime = parseFloat(progressBar.value);
        if (!isNaN(seekTime)) {
            audioPlayer.currentTime = seekTime;
            currentTimeElement.textContent = formatTime(seekTime);
        }
    }
    
    // Seek relative to current position (positive or negative seconds)
    function seekRelative(seconds) {
        if (!audioPlayer) return;
        
        const newTime = Math.max(0, Math.min(audioPlayer.duration, audioPlayer.currentTime + seconds));
        audioPlayer.currentTime = newTime;
        progressBar.value = newTime;
        currentTimeElement.textContent = formatTime(newTime);
    }
    
    // Toggle mute
    function toggleMute() {
        if (!audioPlayer) return;
        
        if (audioPlayer.volume === 0 || audioPlayer.muted) {
            audioPlayer.muted = false;
            audioPlayer.volume = lastVolume;
            volumeSlider.value = lastVolume;
            volumeBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
            audioMuted = false;
        } else {
            lastVolume = audioPlayer.volume;
            audioPlayer.volume = 0;
            volumeSlider.value = 0;
            volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            audioMuted = true;
        }
    }
    
    // Change volume
    function changeVolume() {
        if (!audioPlayer) return;
        
        const volume = parseFloat(volumeSlider.value);
        audioPlayer.volume = volume;
        
        // Update volume icon
        if (volume === 0) {
            volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            audioMuted = true;
        } else {
            volumeBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
            audioMuted = false;
        }
        
        lastVolume = volume;
    }
    
    // Change playback speed
    function changeSpeed() {
        if (!audioPlayer) return;
        
        playbackSpeed = parseFloat(speedSelector.value);
        audioPlayer.playbackRate = playbackSpeed;
    }
    
    // Toggle minimize player
    function toggleMinimize() {
        if (!audioContainer) return;
        
        audioContainer.classList.toggle('minimized');
        
        // Update minimize button icon
        const icon = minimizeToggle.querySelector('i');
        if (audioContainer.classList.contains('minimized')) {
            icon.className = 'fas fa-chevron-up';
            floatingPlayBtn.classList.remove('hidden');
        } else {
            icon.className = 'fas fa-chevron-down';
            floatingPlayBtn.classList.add('hidden');
        }
    }
    
    // Load chapter audio
    function loadChapterAudio(chapterId, audioPath, chapterTitle) {
        if (!audioPlayer) initialize();
        
        // If already playing this chapter, just return
        if (currentChapterId === chapterId && currentAudioPath === audioPath) {
            if (audioContainer.classList.contains('hidden')) {
                audioContainer.classList.remove('hidden');
                floatingPlayBtn.classList.remove('hidden');
            }
            return;
        }
        
        // Log the audio path for debugging
        console.log("Loading audio:", {
            chapterId: chapterId,
            audioPath: audioPath,
            chapterTitle: chapterTitle
        });
        
        // If another chapter is playing, pause it
        if (isPlaying) {
            audioPlayer.pause();
            isPlaying = false;
            updatePlayPauseIcon();
        }
        
        // Set current chapter info
        currentChapterId = chapterId;
        currentAudioPath = audioPath;
        
        // Update chapter title
        if (audioChapterTitle) {
            audioChapterTitle.textContent = chapterTitle || `Chapter ${chapterId}`;
        }
        
        // Set audio source
        audioPlayer.src = audioPath;
        
        // Add error handler to log issues with audio loading
        audioPlayer.onerror = function(e) {
            console.error("Audio error:", e);
            console.error("Audio error details:", {
                error: audioPlayer.error,
                src: audioPlayer.src,
                readyState: audioPlayer.readyState
            });
            
            // Show error message to user
            if (audioContainer && !audioContainer.classList.contains('hidden')) {
                let errorMsg = document.createElement('div');
                errorMsg.className = 'audio-error-message';
                errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error loading audio. Please try again later.';
                errorMsg.style.color = '#ff6b6b';
                errorMsg.style.padding = '10px';
                errorMsg.style.textAlign = 'center';
                
                // Insert error before controls
                const controls = audioContainer.querySelector('.audio-controls');
                if (controls) {
                    audioContainer.insertBefore(errorMsg, controls);
                } else {
                    audioContainer.appendChild(errorMsg);
                }
            }
        };
        
        // Load audio
        audioPlayer.load();
        
        // Reset progress bar and time display
        progressBar.value = 0;
        currentTimeElement.textContent = '0:00';
        
        // Try to restore last played position
        const lastPosition = getAudioProgress(chapterId);
        if (lastPosition) {
            try {
                audioPlayer.currentTime = lastPosition;
                progressBar.value = lastPosition;
                currentTimeElement.textContent = formatTime(lastPosition);
            } catch (err) {
                console.warn('Could not set audio position:', err);
            }
        }
        
        // Show audio player
        audioContainer.classList.remove('hidden');
        audioContainer.classList.remove('minimized');
        floatingPlayBtn.classList.add('hidden');
        
        // Update minimize button icon
        minimizeToggle.querySelector('i').className = 'fas fa-chevron-down';
    }
    
    // Format seconds to MM:SS
    function formatTime(seconds) {
        if (isNaN(seconds) || seconds === Infinity) return '0:00';
        
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Save audio progress to session storage
    function saveAudioProgress(chapterId, position) {
        try {
            const progressData = JSON.parse(sessionStorage.getItem('audioProgress') || '{}');
            progressData[chapterId] = position;
            sessionStorage.setItem('audioProgress', JSON.stringify(progressData));
        } catch (err) {
            console.warn('Could not save audio progress:', err);
        }
    }
    
    // Get saved audio progress from session storage
    function getAudioProgress(chapterId) {
        try {
            const progressData = JSON.parse(sessionStorage.getItem('audioProgress') || '{}');
            return progressData[chapterId] || 0;
        } catch (err) {
            console.warn('Could not get audio progress:', err);
            return 0;
        }
    }
    
    // Public methods
    function play() {
        if (audioPlayer) {
            audioPlayer.play().catch(err => {
                console.error('Play failed:', err);
            });
        }
    }
    
    function pause() {
        if (audioPlayer) {
            audioPlayer.pause();
        }
    }
    
    function stop() {
        if (audioPlayer) {
            audioPlayer.pause();
            audioPlayer.currentTime = 0;
            progressBar.value = 0;
            currentTimeElement.textContent = '0:00';
            isPlaying = false;
            updatePlayPauseIcon();
        }
    }
    
    function isAvailable() {
        return isAudioInitialized && audioPlayer;
    }
    
    function seekTo(time) {
        if (audioPlayer) {
            audioPlayer.currentTime = time;
            progressBar.value = time;
            currentTimeElement.textContent = formatTime(time);
        }
    }
    
    function getCurrentTime() {
        return audioPlayer ? audioPlayer.currentTime : 0;
    }
    
    function getDuration() {
        return audioPlayer ? audioPlayer.duration : 0;
    }
    
    function isCurrentlyPlaying() {
        return isPlaying;
    }
    
    function isAudioLoaded() {
        return audioPlayer && audioPlayer.readyState > 0;
    }
    
    function getCurrentChapterId() {
        return currentChapterId;
    }
    
    // Initialize only when explicitly called
    // We'll let the digital-book.js handle initialization when appropriate
    // rather than auto-initializing on page load

    function togglePlay() {
        if (!audioPlayer) return;
        
        // Completely stop background audio if it's playing
        if (window.BackgroundAudio) {
            // Use pause() to stop the audio
            if (typeof window.BackgroundAudio.pause === 'function') {
                window.BackgroundAudio.pause();
            }
            
            // Make sure it stays off even if user changes pages and comes back
            try {
                // Update the saved state to not playing
                const settings = JSON.parse(localStorage.getItem('bgAudioSettings') || '{}');
                settings.playing = false;
                localStorage.setItem('bgAudioSettings', JSON.stringify(settings));
            } catch (err) {
                console.warn('Could not update background audio settings:', err);
            }
        }
        
        if (audioPlayer.paused) {
            audioPlayer.play().catch(err => {
                console.error('Play failed:', err);
            });
        } else {
            audioPlayer.pause();
        }
        
        isPlaying = !audioPlayer.paused;
        updatePlayPauseIcon();
    }
});