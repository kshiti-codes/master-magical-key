// public/js/components/subscription-modal.js
document.addEventListener('DOMContentLoaded', function() {
    // Check if user has already dismissed the modal
    function shouldShowModal() {
        const lastShown = localStorage.getItem('subscriptionModalLastShown');
        const dontShowAgain = localStorage.getItem('subscriptionModalDontShow');
        
        // If user checked "don't show again", respect that
        if (dontShowAgain === 'true') {
            return false;
        }
        
        // If we have a record of last shown time
        if (lastShown) {
            const daysSinceLastShown = (Date.now() - parseInt(lastShown)) / (1000 * 60 * 60 * 24);
            // Only show once every 7 days if not explicitly dismissed
            return daysSinceLastShown >= 7;
        }
        
        // First time visitor should see the modal
        return true;
    }
    
    // Show modal function
    function showSubscriptionModal() {
        // Only proceed if we should show the modal
        if (!shouldShowModal()) {
            return;
        }
        
        const modal = document.getElementById('subscriptionModal');
        if (!modal) return;
        
        // Show after a delay
        setTimeout(() => {
            modal.classList.add('show');
            
            // Record that we showed the modal
            localStorage.setItem('subscriptionModalLastShown', Date.now().toString());
        }, 2000); // 2 second delay
    }
    
    // Close modal function
    function closeSubscriptionModal() {
        const modal = document.getElementById('subscriptionModal');
        if (!modal) return;
        
        modal.classList.remove('show');
        
        // Check if "don't show again" is checked
        const dontShowCheckbox = document.getElementById('dontShowAgain');
        if (dontShowCheckbox && dontShowCheckbox.checked) {
            localStorage.setItem('subscriptionModalDontShow', 'true');
        }
    }
    
    // Set up event listeners
    function setupModalListeners() {
        const modal = document.getElementById('subscriptionModal');
        if (!modal) return;
        
        // Close button click
        const closeBtn = modal.querySelector('.subscription-modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeSubscriptionModal);
        }
        
        // Click outside to close
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeSubscriptionModal();
            }
        });
        
        // Don't show again checkbox
        const dontShowCheckbox = document.getElementById('dontShowAgain');
        if (dontShowCheckbox) {
            dontShowCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    localStorage.setItem('subscriptionModalDontShow', 'true');
                } else {
                    localStorage.setItem('subscriptionModalDontShow', 'false');
                }
            });
        }
        
        // Subscribe buttons
        const subscribeButtons = document.querySelectorAll('.btn-subscribe');
        subscribeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Clear "don't show again" setting when user engages with a plan
                localStorage.removeItem('subscriptionModalDontShow');
            });
        });
    }
    
    // Initialize modal
    function initSubscriptionModal() {
        setupModalListeners();
        showSubscriptionModal();
    }
    
    // Reset modal for testing (can be called from console)
    window.resetSubscriptionModal = function() {
        localStorage.removeItem('subscriptionModalLastShown');
        localStorage.removeItem('subscriptionModalDontShow');
        console.log('Subscription modal preferences reset. Refresh to see the modal.');
    };
    
    // Initialize
    initSubscriptionModal();
});