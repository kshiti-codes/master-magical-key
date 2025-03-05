// Menu functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Menu JS loaded');
    
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const menuOverlay = document.getElementById('menuOverlay');
    const closeMenuBtn = document.getElementById('closeMenuBtn');
    
    console.log('Hamburger button:', hamburgerBtn);
    console.log('Menu overlay:', menuOverlay);
    console.log('Close button:', closeMenuBtn);
    
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function(e) {
            console.log('Hamburger clicked');
            if (menuOverlay) {
                menuOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    }
    
    if (closeMenuBtn) {
        closeMenuBtn.addEventListener('click', function(e) {
            console.log('Close button clicked');
            if (menuOverlay) {
                menuOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    if (menuOverlay) {
        menuOverlay.addEventListener('click', function(e) {
            console.log('Overlay clicked', e.target === menuOverlay);
            if (e.target === menuOverlay) {
                menuOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
});