// public/js/components/magical-door.js
document.addEventListener('DOMContentLoaded', function() {
    // Create particles
    createDoorParticles();
    
    // Check if magical door exists on the page
    const magicalDoor = document.getElementById('magicalDoor');
    const doorContent = document.getElementById('doorContent');
    const titleContainer = document.querySelector('.title-container');
    
    if (magicalDoor && doorContent) {
        magicalDoor.addEventListener('click', function() {
            if (this.classList.contains('open')) {
                // Close door
                doorContent.classList.remove('visible');

                // Hide any audio elements
                const floatingAudioBtn = document.getElementById('floatingAudioBtn');
                if (floatingAudioBtn) {
                    floatingAudioBtn.classList.add('hidden');
                }
                
                const audioPlayer = document.getElementById('chapterAudioPlayer');
                if (audioPlayer) {
                    audioPlayer.classList.add('hidden');
                }
                
                // Pause any playing audio
                if (window.ChapterAudio && window.ChapterAudio.isCurrentlyPlaying()) {
                    window.ChapterAudio.pause();
                }
                if (titleContainer) {
                    titleContainer.classList.remove('title-exit');
                }
                document.body.classList.remove('door-opened');
                
                setTimeout(() => {
                    this.classList.remove('open');
                }, 300);
            } else {
                // Open door
                this.classList.add('open');
                if (titleContainer) {
                    titleContainer.classList.add('title-exit');
                }
                document.body.classList.add('door-opened');
                
                setTimeout(() => {
                    doorContent.classList.add('visible');
                    // Explicitly check for any audio elements that should now be visible
                    if (window.ChapterAudio && typeof window.fetchChapterPages === 'function') {
                        // The book is now open, audio can be initialized if needed
                        if (chapterHasAudio && chapterAudioInfo) {
                            const floatingAudioBtn = document.getElementById('floatingAudioBtn');
                            if (floatingAudioBtn) {
                                floatingAudioBtn.classList.remove('hidden');
                            }
                        }
                    }
                }, 800);
            }
        });
    }
});

// Create door particles
function createDoorParticles() {
    const particlesContainer = document.getElementById('doorParticles');
    if (!particlesContainer) return;
    
    // Clear existing particles
    particlesContainer.innerHTML = '';
    
    // Create new particles
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.className = 'door-particle';
        
        // Random position
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.top = `${Math.random() * 100}%`;
        
        // Random size
        const size = 3 + Math.random() * 4;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        
        // Random animation duration
        const duration = 5 + Math.random() * 5;
        particle.style.animation = `float-particle ${duration}s infinite ease-in-out`;
        
        // Random delay
        particle.style.animationDelay = `${Math.random() * 5}s`;
        
        particlesContainer.appendChild(particle);
    }
}