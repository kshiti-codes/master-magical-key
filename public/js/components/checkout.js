// public/js/components/checkout.js
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkout-form');
    const paypalButton = document.querySelector('.btn-paypal');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            // Important: Don't prevent default form submission
            
            // Show a loading indicator
            if (paypalButton) {
                paypalButton.disabled = true;
                paypalButton.innerHTML = '<div class="spinner-border spinner-border-sm text-white me-2" role="status"></div> Processing...';
            }
            
            // Create a loading overlay
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'payment-loading-overlay';
            loadingOverlay.innerHTML = `
                <div class="payment-loading-content">
                    <div class="magical-spinner"></div>
                    <p>Preparing your mystical journey...</p>
                </div>
            `;
            document.body.appendChild(loadingOverlay);
            
            // Let the form submit naturally - don't add any delays or timeouts here
        });
    }
    
    // Animate steps
    const steps = document.querySelectorAll('.checkout-step');
    if (steps.length > 0) {
        steps.forEach((step, index) => {
            setTimeout(() => {
                step.style.opacity = "1";
                step.style.transform = "translateY(0)";
            }, 300 + (index * 200));
        });
    }
    
    // Highlight table rows on hover
    const tableRows = document.querySelectorAll('.order-table tbody tr');
    if (tableRows.length > 0) {
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = "rgba(138, 43, 226, 0.1)";
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = "";
            });
        });
    }
});