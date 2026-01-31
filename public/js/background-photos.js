document.addEventListener('DOMContentLoaded', function() {
    const photoCount = 24;
    const currentPath = window.location.pathname;
    
    // Only show on these 3 pages
    const showPhotos = currentPath.includes('/keyholder') || 
                      currentPath.includes('/faq') ||
                      currentPath.includes('/framework') ||
                      currentPath.includes('/about');
    
    if (!showPhotos) {
        return;
    }
    
    const photoContainer = document.createElement('div');
    photoContainer.id = 'side-photos-container';
    document.body.insertBefore(photoContainer, document.body.firstChild);
    
    // Fixed positions in side spaces (left and right)
    const sidePositions = [
        // Left side
        { side: 'left', left: '2%', top: '15%', size: 150, rotation: -8 },
        { side: 'left', left: '1%', top: '40%', size: 150, rotation: 12 },
        { side: 'left', left: '2.5%', top: '65%', size: 150, rotation: -5 },
        
        // Right side
        { side: 'right', right: '2%', top: '20%', size: 150, rotation: 10 },
        { side: 'right', right: '1.5%', top: '45%', size: 150, rotation: -12 },
        { side: 'right', right: '2%', top: '70%', size: 150, rotation: 7 }
    ];
    
    // Select 6 random photos
    const selectedPhotos = [];
    while (selectedPhotos.length < 6) {
        const randomIndex = Math.floor(Math.random() * photoCount) + 1;  
        if (!selectedPhotos.includes(randomIndex)) {
            selectedPhotos.push(randomIndex);
        }
    }
    
    selectedPhotos.forEach((photoNum, index) => {
        const img = document.createElement('img');
        img.src = `/photos/photo${photoNum}.png`;
        img.classList.add('side-photo');
        
        const pos = sidePositions[index];
        if (pos.left) img.style.left = pos.left;
        if (pos.right) img.style.right = pos.right;
        img.style.top = pos.top;
        img.style.width = pos.size + 'px';
        img.style.transform = `rotate(${pos.rotation}deg)`;
        img.style.animationDelay = (index * 0.3) + 's';
        
        photoContainer.appendChild(img);
    });
});