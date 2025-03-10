// Mystical Transitions Module
const MysticalTransitions = {
    // Initialize transitions on the page
    init: function(options = {}) {
        // Default options
        const settings = {
            pageTransition: true,
            linkTransition: true,
            cardAnimation: true,
            transitionLinks: 'a[href*="/login"], a[href*="/register"], a[href*="/password"]',
            cardSelector: '.mystical-card',
            mainContentSelector: 'main',
            fadeSpeed: 300
        };
        
        // Merge user options
        Object.assign(settings, options);

        // Track if animations have been applied already to avoid double animations
        if (window.animationsApplied) return;
        window.animationsApplied = true;
        
        // Add page transition animation
        if (settings.pageTransition) {
            const mainContent = document.querySelector(settings.mainContentSelector);
            if (mainContent) {
                mainContent.classList.add('fade-transition');
            }
        }
        
        // Add card animations on load/reload
        if (settings.cardAnimation) {
            const mysticalCard = document.querySelector(settings.cardSelector);
            if (mysticalCard) {
                // Reset animation to trigger it again
                mysticalCard.style.animation = 'none';
                mysticalCard.offsetHeight; // Trigger reflow
                mysticalCard.style.animation = 'cardAppear 0.6s ease-out';
            }
        }
        
        // Add smooth transitions for links
        if (settings.linkTransition) {
            const links = document.querySelectorAll(settings.transitionLinks);
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const card = document.querySelector(settings.cardSelector);
                    
                    if (card) {
                        // Fade out
                        card.style.transition = `all ${settings.fadeSpeed/1000}s ease`;
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        
                        // Navigate after animation
                        setTimeout(() => {
                            window.location.href = this.href;
                        }, settings.fadeSpeed);
                    } else {
                        window.location.href = this.href;
                    }
                });
            });
        }
    }
};

// Add CSS if not already added
if (!document.getElementById('mystical-transitions-css')) {
    const style = document.createElement('style');
    style.id = 'mystical-transitions-css';
    style.textContent = `
        .fade-transition {
            animation: fadeTransition 0.5s ease-out;
        }
        
        @keyframes fadeTransition {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .mystical-card {
            animation: cardAppear 0.6s ease-out;
        }
        
        @keyframes cardAppear {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.97);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    `;
    document.head.appendChild(style);
}