// public/js/components/background-audio.js
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on homepage
    const isHomePage = window.location.pathname === '/' || window.location.pathname === '/home';
    if (!isHomePage) return;
    
    // Audio elements and tracking variables
    let bgAudio = null;
    let bgAudioPlaying = false;
    let bgAudioInitialized = false;
    let currentTrack = 'ambient1';
    let bgVolume = 0.5;
    let autoplayEnabled = true;
    let settingsVisible = false;
    
    // Audio tracks available (these would be your actual audio files)
    const audioTracks = {
        'ambient1': {
            name: 'Cosmic Mystery',
            path: '/chapter-audio/universe.mp3'
        },
        'ambient2': {
            name: 'Enchanted Forest',
            path: '/audio/background/enchanted-forest.mp3'
        },
        'ambient3': {
            name: 'Celestial Harmony',
            path: '/audio/background/celestial-harmony.mp3'
        },
        'ambient4': {
            name: 'Crystal Dreams',
            path: '/audio/background/crystal-dreams.mp3'
        }
    };
    
    // Initialize background audio
    function initBackgroundAudio() {
        if (bgAudioInitialized) return true;
        
        createAudioToggle();
        createSettingsPanel();
        createAudioElement();
        loadSettings();
        
        // Start playing by default
        if (autoplayEnabled) {
            // Try to play immediately
            if (bgAudio) {
                bgAudio.play().then(() => {
                    bgAudioPlaying = true;
                    const audioToggle = document.getElementById('bgAudioToggle');
                    if (audioToggle) audioToggle.classList.add('active');
                }).catch(err => {
                    // Browser likely blocked autoplay
                    console.warn('Autoplay blocked:', err);
                    bgAudioPlaying = false;
                    
                    // Show a subtle notification about autoplay being blocked
                    showAutoplayNotice();
                });
            }
        }
        
        bgAudioInitialized = true;
        return true;
    }
    
    // Create background audio toggle button
    function createAudioToggle() {
        const audioToggle = document.createElement('div');
        audioToggle.className = 'background-audio-toggle';
        audioToggle.id = 'bgAudioToggle';
        audioToggle.innerHTML = `
            <div class="background-audio-pulse"></div>
            <div class="background-audio-icon">
                <i class="fas fa-music"></i>
                <div class="equalizer">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        
        document.body.appendChild(audioToggle);
        
        // Add click event
        audioToggle.addEventListener('click', function(e) {
            // Check if the settings panel should be toggled
            if (e.ctrlKey || e.metaKey) {
                toggleSettingsPanel();
            } else {
                toggleBackgroundAudio();
            }
        });
        
        // Long press for settings on mobile
        let pressTimer;
        audioToggle.addEventListener('touchstart', function() {
            pressTimer = window.setTimeout(function() {
                toggleSettingsPanel();
            }, 500);
        });
        
        audioToggle.addEventListener('touchend', function() {
            clearTimeout(pressTimer);
        });
        
        // Double click for settings
        audioToggle.addEventListener('dblclick', function(e) {
            e.preventDefault();
            toggleSettingsPanel();
        });
    }
    
    // Create settings panel
    function createSettingsPanel() {
        const settingsPanel = document.createElement('div');
        settingsPanel.className = 'background-audio-settings';
        settingsPanel.id = 'bgAudioSettings';
        
        // Generate track options
        let trackOptions = '';
        for (const key in audioTracks) {
            trackOptions += `<option value="${key}">${audioTracks[key].name}</option>`;
        }
        
        settingsPanel.innerHTML = `
            <h4>Mystical Ambient Audio</h4>
            <div class="audio-settings-controls">
                <div class="audio-setting-row">
                    <span>Volume</span>
                    <div class="audio-volume-control">
                        <i class="fas fa-volume-down"></i>
                        <input type="range" class="audio-bg-volume" id="bgAudioVolume" min="0" max="1" step="0.01" value="0.5">
                        <i class="fas fa-volume-up"></i>
                    </div>
                </div>
                <div class="audio-setting-row">
                    <span>Auto-play</span>
                    <label class="audio-toggle-switch">
                        <input type="checkbox" id="bgAudioAutoplay" checked>
                        <span class="audio-toggle-slider"></span>
                    </label>
                </div>
                <div class="audio-setting-row">
                    <span>Track</span>
                    <select class="audio-select-track" id="bgAudioTrack">
                        ${trackOptions}
                    </select>
                </div>
            </div>
        `;
        
        document.body.appendChild(settingsPanel);
        
        // Add event listeners
        document.getElementById('bgAudioVolume').addEventListener('input', function() {
            setVolume(this.value);
        });
        
        document.getElementById('bgAudioAutoplay').addEventListener('change', function() {
            autoplayEnabled = this.checked;
            saveSettings();
        });
        
        document.getElementById('bgAudioTrack').addEventListener('change', function() {
            changeTrack(this.value);
        });
        
        // Close settings when clicking outside
        document.addEventListener('click', function(e) {
            const settingsPanel = document.getElementById('bgAudioSettings');
            const audioToggle = document.getElementById('bgAudioToggle');
            
            if (settingsVisible && 
                settingsPanel && 
                audioToggle &&
                !settingsPanel.contains(e.target) && 
                !audioToggle.contains(e.target)) {
                
                toggleSettingsPanel(false);
            }
        });
    }
    
    // Create audio element
    function createAudioElement() {
        bgAudio = document.createElement('audio');
        bgAudio.id = 'bgAudio';
        bgAudio.loop = true;
        
        // Default track
        bgAudio.src = audioTracks[currentTrack].path;
        
        // Audio element events
        bgAudio.addEventListener('ended', function() {
            // Just in case loop doesn't work
            if (bgAudioPlaying) {
                bgAudio.currentTime = 0;
                bgAudio.play().catch(err => console.warn('Auto-replay failed:', err));
            }
        });
        
        // Set initial volume
        bgAudio.volume = bgVolume;
        
        // Append to document
        document.body.appendChild(bgAudio);
    }
    
    // Toggle background audio play/pause
    function toggleBackgroundAudio() {
        if (!bgAudio) return;
        
        const audioToggle = document.getElementById('bgAudioToggle');
        
        if (bgAudioPlaying) {
            // Pause audio
            bgAudio.pause();
            bgAudioPlaying = false;
            if (audioToggle) {
                audioToggle.classList.remove('active');
            }
        } else {
            // Play audio
            bgAudio.play().catch(err => {
                console.warn('Playback failed:', err);
                // May fail due to browser autoplay restrictions
                showAutoplayNotice();
            });
            
            bgAudioPlaying = true;
            if (audioToggle) {
                audioToggle.classList.add('active');
            }
        }
        
        saveSettings();
    }
    
    // Toggle settings panel
    function toggleSettingsPanel(show = null) {
        const settingsPanel = document.getElementById('bgAudioSettings');
        if (!settingsPanel) return;
        
        // If show is explicitly set, use that value, otherwise toggle
        settingsVisible = show !== null ? show : !settingsVisible;
        
        if (settingsVisible) {
            settingsPanel.classList.add('visible');
        } else {
            settingsPanel.classList.remove('visible');
        }
    }
    
    // Show notice about autoplay restrictions
    function showAutoplayNotice() {
        // Create a temporary notification
        const notice = document.createElement('div');
        notice.style.position = 'fixed';
        notice.style.bottom = '80px';
        notice.style.right = '20px';
        notice.style.background = 'rgba(10, 10, 30, 0.9)';
        notice.style.padding = '10px 15px';
        notice.style.borderRadius = '5px';
        notice.style.color = 'white';
        notice.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.3)';
        notice.style.zIndex = '100';
        notice.style.maxWidth = '250px';
        notice.style.fontSize = '0.9rem';
        notice.style.animation = 'fadeIn 0.3s forwards';
        
        notice.innerHTML = 'Your browser blocked autoplay. Click the music button again to start the mystical audio.';
        
        document.body.appendChild(notice);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notice.style.animation = 'fadeOut 0.3s forwards';
            setTimeout(() => {
                if (notice.parentNode) {
                    notice.parentNode.removeChild(notice);
                }
            }, 300);
        }, 5000);
        
        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(20px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Set background audio volume
    function setVolume(volume) {
        if (!bgAudio) return;
        
        volume = parseFloat(volume);
        bgVolume = volume;
        bgAudio.volume = volume;
        
        // Update input value for consistency
        const volumeInput = document.getElementById('bgAudioVolume');
        if (volumeInput) {
            volumeInput.value = volume;
        }
        
        saveSettings();
    }
    
    // Change background audio track
    function changeTrack(track) {
        if (!bgAudio || !audioTracks[track]) return;
        
        const wasPlaying = bgAudioPlaying;
        
        // Pause current track
        bgAudio.pause();
        
        // Set new track
        currentTrack = track;
        bgAudio.src = audioTracks[track].path;
        
        // Update track selector
        const trackSelect = document.getElementById('bgAudioTrack');
        if (trackSelect) {
            trackSelect.value = track;
        }
        
        // Resume playing if it was playing before
        if (wasPlaying) {
            bgAudio.play().catch(err => console.warn('Track change play failed:', err));
        }
        
        saveSettings();
    }
    
    // Save settings to localStorage
    function saveSettings() {
        const settings = {
            volume: bgVolume,
            track: currentTrack,
            autoplay: autoplayEnabled,
            playing: bgAudioPlaying
        };
        
        try {
            localStorage.setItem('bgAudioSettings', JSON.stringify(settings));
        } catch (err) {
            console.warn('Could not save audio settings:', err);
        }
    }
    
    // Load settings from localStorage
    function loadSettings() {
        try {
            const settings = JSON.parse(localStorage.getItem('bgAudioSettings'));
            
            if (settings) {
                // Set volume
                if (settings.volume !== undefined) {
                    bgVolume = parseFloat(settings.volume);
                    if (bgAudio) bgAudio.volume = bgVolume;
                    
                    const volumeInput = document.getElementById('bgAudioVolume');
                    if (volumeInput) volumeInput.value = bgVolume;
                }
                
                // Set track
                if (settings.track && audioTracks[settings.track]) {
                    currentTrack = settings.track;
                    if (bgAudio) bgAudio.src = audioTracks[currentTrack].path;
                    
                    const trackSelect = document.getElementById('bgAudioTrack');
                    if (trackSelect) trackSelect.value = currentTrack;
                }
                
                // Set autoplay
                if (settings.autoplay !== undefined) {
                    autoplayEnabled = settings.autoplay;
                    
                    const autoplayCheckbox = document.getElementById('bgAudioAutoplay');
                    if (autoplayCheckbox) autoplayCheckbox.checked = autoplayEnabled;
                } else {
                    // Default to true if not set previously
                    autoplayEnabled = true;
                    
                    const autoplayCheckbox = document.getElementById('bgAudioAutoplay');
                    if (autoplayCheckbox) autoplayCheckbox.checked = true;
                }
                
                // We'll handle actual playback in the init function
            } else {
                // Default to autoplay enabled if no settings found
                autoplayEnabled = true;
                
                const autoplayCheckbox = document.getElementById('bgAudioAutoplay');
                if (autoplayCheckbox) autoplayCheckbox.checked = true;
            }
        } catch (err) {
            console.warn('Could not load audio settings:', err);
            
            // Default to autoplay enabled if error
            autoplayEnabled = true;
            
            const autoplayCheckbox = document.getElementById('bgAudioAutoplay');
            if (autoplayCheckbox) autoplayCheckbox.checked = true;
        }
    }
    
    // Handle chapter audio interaction
    function handleChapterAudioInteraction() {
        // If chapter audio gets loaded at any point, make sure background audio stops
        document.addEventListener('DOMNodeInserted', function(e) {
            if (e.target.id === 'chapterAudioPlayer' || 
                e.target.id === 'chapterAudio' ||
                e.target.id === 'floatingAudioBtn') {
                
                // Check if the element is visible/active
                if (!e.target.classList.contains('hidden')) {
                    // Stop background audio completely
                    if (bgAudio && bgAudioPlaying) {
                        bgAudio.pause();
                        bgAudioPlaying = false;
                        
                        const audioToggle = document.getElementById('bgAudioToggle');
                        if (audioToggle) audioToggle.classList.remove('active');
                        
                        // Update localStorage to remember it's stopped
                        saveSettings();
                    }
                }
            }
        });
        
        // Listen for chapter audio player initialization
        if (window.ChapterAudio) {
            // When chapter audio loads, stop background audio
            const originalLoadChapterAudio = window.ChapterAudio.loadChapterAudio;
            if (originalLoadChapterAudio) {
                window.ChapterAudio.loadChapterAudio = function() {
                    // Stop background audio
                    if (bgAudio && bgAudioPlaying) {
                        bgAudio.pause();
                        bgAudioPlaying = false;
                        
                        const audioToggle = document.getElementById('bgAudioToggle');
                        if (audioToggle) audioToggle.classList.remove('active');
                        
                        // Update localStorage to remember it's stopped
                        saveSettings();
                    }
                    
                    // Call original function
                    return originalLoadChapterAudio.apply(this, arguments);
                };
            }
        }
    }
    
    // Define global methods
    window.BackgroundAudio = {
        initialize: initBackgroundAudio,
        play: function() {
            if (!bgAudio || bgAudioPlaying) return;
            
            bgAudio.play().catch(err => console.warn('Play failed:', err));
            bgAudioPlaying = true;
            
            const audioToggle = document.getElementById('bgAudioToggle');
            if (audioToggle) audioToggle.classList.add('active');
            
            saveSettings();
        },
        pause: function() {
            if (!bgAudio || !bgAudioPlaying) return;
            
            bgAudio.pause();
            bgAudioPlaying = false;
            
            const audioToggle = document.getElementById('bgAudioToggle');
            if (audioToggle) audioToggle.classList.remove('active');
            
            saveSettings();
        },
        toggle: toggleBackgroundAudio,
        setVolume: setVolume,
        changeTrack: changeTrack
    };
    
    // Initialize background audio
    initBackgroundAudio();
    
    // Handle chapter audio interaction
    handleChapterAudioInteraction();
    
    // Handle page visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, pause audio to conserve bandwidth
            if (bgAudio && bgAudioPlaying) {
                bgAudio.pause();
                // Don't update bgAudioPlaying so it can resume when visible
            }
        } else {
            // Page is visible again, resume if it was playing
            if (bgAudio && bgAudioPlaying) {
                bgAudio.play().catch(err => console.warn('Visibility resume failed:', err));
            }
        }
    });
    
    // Handle page unload
    window.addEventListener('beforeunload', function() {
        // Save current playback state
        saveSettings();
    });
});