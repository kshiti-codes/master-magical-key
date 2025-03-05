document.addEventListener('DOMContentLoaded', function() {
    const starsContainer = document.createElement('div');
    starsContainer.className = 'stars';
    document.body.appendChild(starsContainer);
    
    // Adjust number of stars based on screen size
    const isMobile = window.innerWidth < 768;
    const isSmallMobile = window.innerWidth < 360;
    
    // Create stars - fewer on mobile
    const starCount = isSmallMobile ? 50 : (isMobile ? 100 : 150);
    for (let i = 0; i < starCount; i++) {
        createStar(starsContainer);
    }
    
    // Create floating elements - fewer on mobile
    const floatingElementCount = isSmallMobile ? 3 : (isMobile ? 5 : 10);
    for (let i = 0; i < floatingElementCount; i++) {
        createFloatingElement(starsContainer);
    }
    
    // Handle resize events
    window.addEventListener('resize', debounce(function() {
        // Clear existing elements if screen size changes significantly
        const newIsMobile = window.innerWidth < 768;
        if (newIsMobile !== isMobile) {
            while (starsContainer.firstChild) {
                starsContainer.removeChild(starsContainer.firstChild);
            }
            
            const newStarCount = newIsMobile ? 75 : 150;
            for (let i = 0; i < newStarCount; i++) {
                createStar(starsContainer);
            }
            
            const newFloatingCount = newIsMobile ? 5 : 10;
            for (let i = 0; i < newFloatingCount; i++) {
                createFloatingElement(starsContainer);
            }
        }
    }, 250));
});

function createStar(container) {
    const star = document.createElement('div');
    star.className = 'star';
    
    // Random position
    star.style.left = `${Math.random() * 100}%`;
    star.style.top = `${Math.random() * 100}%`;
    
    // Random size
    const size = Math.random() * 3;
    star.style.width = `${size}px`;
    star.style.height = `${size}px`;
    
    // Random animation delay
    star.style.animationDelay = `${Math.random() * 5}s`;
    
    container.appendChild(star);
}

function createFloatingElement(container) {
    const element = document.createElement('div');
    element.className = 'floating-element';
    
    // Styling
    element.style.position = 'absolute';
    element.style.left = `${Math.random() * 100}%`;
    element.style.top = `${Math.random() * 100}%`;
    
    // Random size between 10px and 30px
    const size = 10 + Math.random() * 20;
    element.style.width = `${size}px`;
    element.style.height = `${size}px`;
    
    // Mystical appearance
    element.style.background = `radial-gradient(circle, 
        rgba(138, 43, 226, ${0.3 + Math.random() * 0.7}), 
        rgba(25, 25, 112, 0) 70%)`;
    element.style.borderRadius = '50%';
    element.style.filter = 'blur(1px)';
    
    // Animation
    element.style.animation = `float ${5 + Math.random() * 10}s infinite ease-in-out`;
    element.style.animationDelay = `${Math.random() * 5}s`;
    
    container.appendChild(element);
}

document.addEventListener('mousemove', function(e) {
    // Check if there's already a mystic glow
    let glow = document.querySelector('.mystic-glow');
    if (!glow) {
        glow = document.createElement('div');
        glow.className = 'mystic-glow';
        document.body.appendChild(glow);
    }
    
    // Position the glow at cursor position
    glow.style.left = `${e.clientX - 75}px`;
    glow.style.top = `${e.clientY - 75}px`;
});

document.addEventListener('DOMContentLoaded', function() {
    // Create nebula container
    const nebulaContainer = document.createElement('div');
    nebulaContainer.className = 'nebula';
    document.body.appendChild(nebulaContainer);
    
    // Create nebula clouds
    for (let i = 0; i < 5; i++) {
        createNebulaCloud(nebulaContainer);
    }
});

function createNebulaCloud(container) {
    const cloud = document.createElement('div');
    cloud.className = 'nebula-cloud';
    
    // Random position
    cloud.style.left = `${Math.random() * 100}%`;
    cloud.style.top = `${Math.random() * 100}%`;
    
    // Random size between 200px and 500px
    const size = 200 + Math.random() * 300;
    cloud.style.width = `${size}px`;
    cloud.style.height = `${size}px`;
    
    // Random color (purple/blue hues)
    const hue = 240 + Math.random() * 60; // blue to purple
    const saturation = 70 + Math.random() * 30;
    const lightness = 30 + Math.random() * 20;
    cloud.style.background = `radial-gradient(ellipse at center, 
        hsla(${hue}, ${saturation}%, ${lightness}%, 0.2) 0%, 
        hsla(${hue}, ${saturation}%, ${lightness}%, 0.1) 50%, 
        transparent 70%)`;
    
    // Animation
    cloud.style.animation = `nebula-float ${30 + Math.random() * 20}s infinite ease-in-out`;
    cloud.style.animationDelay = `${Math.random() * 10}s`;
    
    container.appendChild(cloud);
}

// Add debounce function to prevent excessive executions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
