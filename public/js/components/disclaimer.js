// JavaScript for the disclaimer card functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check if disclaimer has been shown before
    const disclaimerSeen = localStorage.getItem('disclaimer_accepted');
    
    // Only show the disclaimer if it hasn't been accepted before
    if (!disclaimerSeen) {
        showDisclaimerCard();
    }
    
    function showDisclaimerCard() {
        // Create the disclaimer overlay
        const overlay = document.createElement('div');
        overlay.className = 'disclaimer-overlay';
        
        // Create the card
        const card = document.createElement('div');
        card.className = 'disclaimer-card';
        
        // Add close button
        const closeButton = document.createElement('button');
        closeButton.className = 'disclaimer-close';
        closeButton.innerHTML = '×';
        closeButton.addEventListener('click', hideDisclaimerCard);
        
        // Add content
        const title = document.createElement('h2');
        title.className = 'disclaimer-title';
        title.textContent = 'Reader/Listener Discretion is Enchanted ⚡️';
        
        const content = document.createElement('div');
        content.className = 'disclaimer-content';
        
        // Add disclaimer content
        content.innerHTML = `
            <p>This book is an initiation, a spell, and a full-body activation. It is raw, explicit, and unapologetically real. Read with an open heart, a grounded spirit, and a willingness to meet yourself fully.</p>
            
            <p>🔥 Swearing is a spell. Words in this book are sharp, intentional, and full of charge. Expect explicit language used unapologetically.</p>
            
            <p>🌑 Shadow work ahead. This content may shake loose what you thought was solid. It will challenge belief systems, question conditioning, and expose illusions. If something triggers you—pause, breathe, and explore why. There is power in the discomfort, and resistance is the key to collecting gold. What you resist holds the treasure—if you are willing to face it.</p>
            
            <div class="disclaimer-warning">
                <h3>💥 🚨 EXTREME TRIGGER WARNING 🚨</h3>
                <p>This book does not shy away from the raw, the real, and the taboo. Read the contents carefully before proceeding.</p>
                
                <p>Topics may include:</p>
                <ul>
                    <li>Trauma (physical, emotional, and psychological)</li>
                    <li>Sexuality & sex magic (explicit adult themes, BDSM, energetic exchange, power play)</li>
                    <li>Identity (gender, orientation, personal sovereignty, self-concept deconstruction)</li>
                    <li>Power dynamics (spiritual, sexual, societal, and psychological control & reclamation)</li>
                    <li>Dismantling oppressive systems (religion, patriarchy, capitalism, colonialism, and beyond)</li>
                </ul>
                
                <p>If any of these topics feel like too much—trust yourself. You are not required to read what you are not ready for.</p>
            </div>
            
            <p>🔥 X-Rated Content. This book contains graphic sexual content, intense self-exploration, and explicit themes. If you are uncomfortable with deep dives into pleasure, power, and primal energy, this book may not be for you.</p>
            
            <h3>🚨 Legal Disclaimer:</h3>
            <ul>
                <li>This book is intended for mature audiences only (18+).</li>
                <li>By continuing, you confirm that you are of legal age in your country and take full responsibility for engaging with this content.</li>
                <li>The author and publisher assume no liability for how you interpret or apply the material.</li>
                <li>This is not a substitute for professional, legal, medical, or therapeutic advice.</li>
            </ul>
            
            <p>✨ Consent is key. Your journey through these pages is your own. Take what serves you, leave what does not, and always return to your own inner wisdom.</p>
            
            <p><em>You have been warned. You have been invited.</em></p>
            
            <p><strong>Proceed with intention—you are now inside the magic.</strong></p>
        `;
        
        // Add accept button
        const acceptButton = document.createElement('button');
        acceptButton.className = 'disclaimer-accept';
        acceptButton.textContent = 'I Accept';
        acceptButton.addEventListener('click', hideDisclaimerCard);
        
        // Assemble all elements
        card.appendChild(closeButton);
        card.appendChild(title);
        card.appendChild(content);
        card.appendChild(acceptButton);
        overlay.appendChild(card);
        
        // Add to document
        document.body.appendChild(overlay);
        
        // Prevent background scrolling
        document.body.style.overflow = 'hidden';
    }
    
    function hideDisclaimerCard() {
        // Record that the user has seen the disclaimer
        localStorage.setItem('disclaimer_accepted', 'true');
        
        // Find and remove the overlay
        const overlay = document.querySelector('.disclaimer-overlay');
        if (overlay) {
            // Add fade out animation
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.5s ease';
            
            // Remove after animation completes
            setTimeout(() => {
                overlay.remove();
                // Restore background scrolling
                document.body.style.overflow = '';
            }, 500);
        }
    }
    
    // Add a reset function for testing purposes
    window.resetDisclaimer = function() {
        localStorage.removeItem('disclaimer_accepted');
        alert('Disclaimer reset. Refresh the page to see it again.');
    };
});