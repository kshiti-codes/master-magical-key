// public/js/components/digital-book.js
document.addEventListener('DOMContentLoaded', function() {
    // Book elements
    let bookInitialized = false;
    const digitalBook = document.getElementById('digitalBook');
    const bookContainer = document.getElementById('bookContainer');
    const closeBookBtn = document.getElementById('closeBookBtn');
    const pageTurner = document.getElementById('pageTurner');
    const leftPage = document.getElementById('leftPage');
    const rightPage = document.getElementById('rightPage');
    const turnerFront = document.getElementById('turnerFront');
    const turnerBack = document.getElementById('turnerBack');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const mobilePrevBtn = document.getElementById('mobilePrevBtn');
    const mobileNextBtn = document.getElementById('mobileNextBtn');
    const mobileCloseBtn = document.getElementById('mobileCloseBtn');
    
    if (!digitalBook || !bookContainer) return;

    // Global variables for server-side pagination
    let chapterMetadata = null;
    let loadedPages = {};
    let currentSpread = 0;
    let totalSpreads = 0;
    let currentChapterId = null;
    let isTurning = false;
    let nextChapterInfo = null;
    
    // Function to find a chapter's page
    window.findChapterPage = function(chapterId) {
        if (!chapterMetadata) return null;
        
        // Convert to number for consistent comparison
        chapterId = parseInt(chapterId);
        
        // For now, just return the first page of the chapter
        // This would need to be updated with real chapter mapping
        if (chapterId === parseInt(chapterMetadata.id)) {
            return 1;
        }
        
        return null;
    };

    // Function to open a specific chapter
    window.openSpecificChapter = function(chapterId) {
        if (!bookInitialized) {
            console.error('Book not initialized yet');
            return false;
        }
        
        // Load the chapter
        loadChapter(chapterId);
        return true;
    };

    // Create a test element to measure text dimensions
    const testElement = document.createElement('div');
    testElement.style.position = 'absolute';
    testElement.style.visibility = 'hidden';
    testElement.style.width = '100%';
    testElement.style.padding = '20px';
    testElement.style.boxSizing = 'border-box';
    testElement.style.fontFamily = "'Rajdhani', sans-serif";
    testElement.style.fontSize = '0.95rem';
    testElement.style.lineHeight = '1.6';
    document.body.appendChild(testElement);
    
    // Load a chapter from the server
    function loadChapter(chapterId) {
        showLoading();
        currentChapterId = chapterId;
        
        // Reset loaded pages
        loadedPages = {};
        
        // Fetch initial pages
        fetchChapterPages(chapterId, 1, function() {
            // Calculate total spreads based on total pages
            totalSpreads = Math.ceil(chapterMetadata.totalPages / (isMobile() ? 1 : 2));
            
            // Default to first spread
            currentSpread = 0;
            
            // Update the display
            updatePageContent();
            updateNavigationButtons();
            
            hideLoading();
        });
    }

    // Fetch pages from the API
    function fetchChapterPages(chapterId, startPage, callback) {
        const perPage = 5; // Number of pages to fetch at once
        
        fetch(`/api/chapters/${chapterId}/pages?page=${startPage}&per_page=${perPage}`)
            .then(response => response.json())
            .then(data => {
                // Store chapter metadata if not already set
                if (!chapterMetadata || chapterMetadata.id !== data.chapter_id) {
                    chapterMetadata = {
                        id: data.chapter_id,
                        title: data.title,
                        totalPages: data.total_pages
                    };
                }
                
                // Store next chapter info
                nextChapterInfo = data.next_chapter;
                
                // Store the loaded pages
                data.pages.forEach(page => {
                    loadedPages[page.page_number] = page.content;
                });
                
                // Call the callback function if provided
                if (typeof callback === 'function') {
                    callback();
                }
                
                // Preload next batch if needed
                if (startPage + perPage < data.total_pages) {
                    setTimeout(() => {
                        fetchChapterPages(chapterId, startPage + perPage);
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error fetching chapter pages:', error);
                hideLoading();
            });
    }

    // Add this function to detect mobile devices
    function isMobile() {
        return window.innerWidth <= 767;
    }
    
    // Create decorative page lines
    function createPageLines() {
        let lines = '<div class="page-lines">';
        for (let i = 1; i <= 9; i++) {
            lines += '<div class="page-line"></div>';
        }
        lines += '</div>';
        return lines;
    }
    
    // Get available height for page content based on current dimensions
    function getPageContentHeight() {
        // Get different heights based on device type
        if (isMobile()) {
            // For mobile, use most of the available height
            const bookLeftPage = document.getElementById('leftPage');
            if (bookLeftPage) {
                const pageHeight = bookLeftPage.clientHeight;
                // Reserve less space for headers and navigation on mobile
                return pageHeight - 70; // Reserve space for title and navigation
            } else {
                return window.innerHeight * 0.7; // Fallback if element not found
            }
        } else {
            // For desktop, use the existing calculation
            const pageHeight = leftPage ? leftPage.clientHeight : 400;
            return pageHeight - 100; // Reserve space for headers and page number
        }
    }
    
    // Initialize book
    function initBook() {
        // Check for chapter ID from URL parameter
        const chapterId = getChapterIdFromUrl();
        
        if (chapterId) {
            // Load the chapter
            loadChapter(chapterId);
        } else {
            // Load a default chapter or introduction
            showIntroduction();
        }
        
        // Check if we need to switch to mobile layout
        toggleMobileLayout();

        // Set up mobile navigation
        const mobilePrevBtn = document.getElementById('mobilePrevBtn');
        const mobileNextBtn = document.getElementById('mobileNextBtn');
        const closeBookBtnMobile = document.getElementById('closeBookBtnMobile');

        if (mobilePrevBtn) {
            mobilePrevBtn.addEventListener('click', turnToPrevPage);
        }

        if (mobileNextBtn) {
            mobileNextBtn.addEventListener('click', turnToNextPage);
        }

        if (closeBookBtnMobile) {
            closeBookBtnMobile.addEventListener('click', function() {
                const doorContent = document.getElementById('doorContent');
                const magicalDoor = document.getElementById('magicalDoor');
                
                if (doorContent) {
                    doorContent.classList.remove('visible');
                    
                    setTimeout(() => {
                        document.body.classList.remove('door-opened');
                        const titleContainer = document.querySelector('.title-container');
                        if (titleContainer) {
                            titleContainer.classList.remove('title-exit');
                        }
                        if (magicalDoor) {
                            magicalDoor.classList.remove('open');
                        }
                    }, 300);
                }
            });
        }
        
        // Make book state accessible to window
        bookInitialized = true;
        window.bookInitialized = bookInitialized;
    }
    
    // Show introduction or default content if no chapter is selected
    function showIntroduction() {
        // You could load a specific introduction chapter here
        // For now, we'll just display a message
        leftPage.innerHTML = `
            <div class="chapter-content">
                <h2 class="chapter-title">Welcome to the Digital Book</h2>
                <div class="chapter-text">
                    <p>Please select a chapter to begin reading.</p>
                </div>
            </div>
        ` + createPageLines();
        
        rightPage.innerHTML = `
            <div class="chapter-content">
                <h2 class="chapter-title">Available Chapters</h2>
                <div class="chapter-text">
                    <p>Explore our chapters from the chapters page.</p>
                </div>
            </div>
        ` + createPageLines();
        
        // Set dummy values for navigation
        chapterMetadata = {
            id: 0,
            title: "Introduction",
            totalPages: 2
        };
        
        totalSpreads = 1;
        currentSpread = 0;
        
        updateNavigationButtons();
    }
    
    // Update page content based on current spread
    function updatePageContent() {
        if (!chapterMetadata) {
            showIntroduction();
            return;
        }
        
        if (isMobile()) {
            // Mobile - show only the current page
            const pageNum = currentSpread + 1;
            
            // Update content in left page only
            if (pageNum <= chapterMetadata.totalPages) {
                // Check if page is loaded
                if (loadedPages[pageNum]) {
                    leftPage.innerHTML = loadedPages[pageNum] + createPageLines();
                } else {
                    leftPage.innerHTML = '<div class="loading-page">Loading...</div>' + createPageLines();
                    // Fetch the missing page
                    fetchChapterPages(currentChapterId, pageNum);
                }
            } else if (nextChapterInfo) {
                // Show next chapter info instead
                leftPage.innerHTML = createNextChapterPage() + createPageLines();
            } else {
                leftPage.innerHTML = createComingSoonPage() + createPageLines();
            }
            
            // Update mobile page indicator
            const pageIndicator = document.getElementById('mobilePageIndicator');
            if (pageIndicator) {
                pageIndicator.textContent = `Page ${pageNum}/${chapterMetadata.totalPages}`;
            }
        } else {
            // Desktop - show spread of two pages
            const leftPageNum = currentSpread * 2 + 1;
            const rightPageNum = leftPageNum + 1;
            
            // Left page content
            if (leftPageNum <= chapterMetadata.totalPages) {
                if (loadedPages[leftPageNum]) {
                    leftPage.innerHTML = loadedPages[leftPageNum] + 
                        `<div class="page-number page-number-left">${leftPageNum}</div>` +
                        createPageLines();
                } else {
                    leftPage.innerHTML = '<div class="loading-page">Loading...</div>' +
                        `<div class="page-number page-number-left">${leftPageNum}</div>` +
                        createPageLines();
                    // Fetch the missing page
                    fetchChapterPages(currentChapterId, leftPageNum);
                }
            } else if (nextChapterInfo) {
                // Show next chapter info on the left page
                leftPage.innerHTML = createNextChapterPage() +
                    `<div class="page-number page-number-left">${leftPageNum}</div>` +
                    createPageLines();
            } else {
                leftPage.innerHTML = createComingSoonPage() + 
                    `<div class="page-number page-number-left">${leftPageNum}</div>` +
                    createPageLines();
            }
            
            // Right page content
            if (rightPageNum <= chapterMetadata.totalPages) {
                if (loadedPages[rightPageNum]) {
                    rightPage.innerHTML = loadedPages[rightPageNum] + 
                        `<div class="page-number page-number-right">${rightPageNum}</div>` +
                        createPageLines();
                } else {
                    rightPage.innerHTML = '<div class="loading-page">Loading...</div>' +
                        `<div class="page-number page-number-right">${rightPageNum}</div>` +
                        createPageLines();
                    // Fetch the missing page
                    fetchChapterPages(currentChapterId, rightPageNum);
                }
            } else if (nextChapterInfo && leftPageNum <= chapterMetadata.totalPages) {
                // Show next chapter info only on right page if left page has content
                rightPage.innerHTML = createNextChapterPage() +
                    `<div class="page-number page-number-right">${rightPageNum}</div>` +
                    createPageLines();
            } else {
                rightPage.innerHTML = createComingSoonPage() + 
                    `<div class="page-number page-number-right">${rightPageNum}</div>` +
                    createPageLines();
            }
        }
    }

    // Function to create the "Purchase Next Chapter" page
    function createNextChapterPage() {
        if (!nextChapterInfo) return createComingSoonPage();
        
        let purchaseButton = '';
        if (nextChapterInfo.is_free || nextChapterInfo.is_purchased) {
            purchaseButton = `<a href="/read/${nextChapterInfo.id}" class="btn btn-portal">Read Now</a>`;
        } else {
            purchaseButton = `<a href="${nextChapterInfo.purchase_url}" class="btn btn-portal">Purchase Chapter</a>`;
        }
        
        return `
            <div class="chapter-content">
                <h2 class="chapter-title">Continue Your Journey</h2>
                <h3 class="chapter-description">Chapter ${nextChapterInfo.id}: ${nextChapterInfo.title}</h3>
                <div class="chapter-purchase">
                    <p>${nextChapterInfo.description}</p>
                    <p class="chapter-price">${nextChapterInfo.is_free ? 
                        '<span class="free-badge">Free</span>' : 
                        '$' + parseFloat(nextChapterInfo.price).toFixed(2) + ' ' + nextChapterInfo.currency}</p>
                    ${purchaseButton}
                </div>
            </div>
        `;
    }

    // Function to create the "Coming Soon" page
    function createComingSoonPage() {
        return `
            <div class="coming-soon">
                <h2 class="chapter-title">More Chapters Coming Soon</h2>
                <div class="coming-soon-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <p>The journey continues with new chapters being prepared for your enlightenment.</p>
                <p>Return soon to discover more cosmic wisdom and revelations.</p>
            </div>
        `;
    }
    
    // Update navigation buttons state
    function updateNavigationButtons() {
        // In mobile view, adjust based on total pages rather than spreads
        if (isMobile()) {
            const currentPage = currentSpread + 1;
            const totalPages = chapterMetadata ? chapterMetadata.totalPages : 0;
            
            if (prevBtn) {
                prevBtn.style.opacity = currentPage > 1 ? "1" : "0.3";
                prevBtn.style.pointerEvents = currentPage > 1 ? "auto" : "none";
            }
            
            if (nextBtn) {
                nextBtn.style.opacity = currentPage < totalPages ? "1" : "0.3";
                nextBtn.style.pointerEvents = currentPage < totalPages ? "auto" : "none";
            }
            
            // Also update mobile buttons
            const mobilePrevBtn = document.getElementById('mobilePrevBtn');
            const mobileNextBtn = document.getElementById('mobileNextBtn');
            
            if (mobilePrevBtn) {
                mobilePrevBtn.style.opacity = currentPage > 1 ? "1" : "0.3";
                mobilePrevBtn.style.pointerEvents = currentPage > 1 ? "auto" : "none";
            }
            
            if (mobileNextBtn) {
                mobileNextBtn.style.opacity = currentPage < totalPages ? "1" : "0.3";
                mobileNextBtn.style.pointerEvents = currentPage < totalPages ? "auto" : "none";
            }
        } else {
            // Desktop view 
            if (prevBtn) {
                prevBtn.style.opacity = currentSpread > 0 ? "1" : "0.3";
                prevBtn.style.pointerEvents = currentSpread > 0 ? "auto" : "none";
            }
            
            if (nextBtn) {
                const maxSpread = totalSpreads - 1;
                nextBtn.style.opacity = currentSpread < maxSpread ? "1" : "0.3";
                nextBtn.style.pointerEvents = currentSpread < maxSpread ? "auto" : "none";
            }
        }
    }

    // Function to toggle between desktop and mobile layouts
    function toggleMobileLayout() {
        const bookContainer = document.getElementById('bookContainer');
        const digitalBook = document.getElementById('digitalBook');
        const bookContentWrapper = document.querySelector('.book-content-wrapper');
        
        if (!bookContainer || !digitalBook || !bookContentWrapper) return;
        
        if (isMobile()) {
            // Switch to mobile layout
            digitalBook.classList.add('mobile-view');
            bookContainer.classList.add('mobile-view');
            bookContentWrapper.classList.add('mobile-view');
            
            // Recalculate spreads for mobile
            if (chapterMetadata) {
                totalSpreads = chapterMetadata.totalPages;
            }
            
            // Update navigation display
            updateMobilePageIndicator();
        } else {
            // Switch to desktop layout
            digitalBook.classList.remove('mobile-view');
            bookContainer.classList.remove('mobile-view');
            bookContentWrapper.classList.remove('mobile-view');
            
            // Recalculate spreads for desktop
            if (chapterMetadata) {
                totalSpreads = Math.ceil(chapterMetadata.totalPages / 2);
            }
        }
        
        // Update the visible content
        updatePageContent();
    }

    // Update mobile page indicator
    function updateMobilePageIndicator() {
        const pageIndicator = document.getElementById('mobilePageIndicator');
        if (pageIndicator && chapterMetadata) {
            const currentPage = currentSpread + 1;
            pageIndicator.textContent = `Page ${currentPage}/${chapterMetadata.totalPages}`;
        }
    }
    
    // Turn to previous spread
    function turnToPrevPage() {
        if (currentSpread > 0 && !isTurning) {
            isTurning = true;
            
            // Set up the page turner for animation
            pageTurner.style.zIndex = "5";
            pageTurner.style.left = "0";
            pageTurner.style.transformOrigin = "right center";
            
            // Copy current left page to turner front (what's visible before turn)
            turnerFront.innerHTML = leftPage.innerHTML;
            
            // Decrement current spread
            currentSpread--;
            
            // Prepare next content (what will be visible after turn)
            updatePageContent();
            
            // Copy the new left page to turner back (what's visible after turn)
            turnerBack.innerHTML = leftPage.innerHTML;
            
            // Start turn animation
            pageTurner.style.transform = "rotateY(-180deg)";
            
            setTimeout(() => {
                // Reset turner to hide it
                pageTurner.style.transition = "none";
                pageTurner.style.transform = "rotateY(0)";
                pageTurner.style.zIndex = "-1";
                
                // Re-enable transition for next animation
                setTimeout(() => {
                    pageTurner.style.transition = "transform 0.6s cubic-bezier(0.645, 0.045, 0.355, 1)";
                    isTurning = false;
                }, 50);
                
                updateNavigationButtons();
            }, 600);
        }
    }
    
    // Turn to next spread
    function turnToNextPage() {
        if (currentSpread < totalSpreads - 1 && !isTurning) {
            isTurning = true;
            
            // Set up the page turner for animation
            pageTurner.style.zIndex = "5";
            pageTurner.style.left = "50%";
            pageTurner.style.transformOrigin = "left center";
            
            // Copy current right page to turner front (what's visible before turn)
            turnerFront.innerHTML = rightPage.innerHTML;
            
            // Increment current spread
            currentSpread++;
            
            // Prepare next content
            updatePageContent();
            
            // Copy the new right page to turner back (what's visible after turn)
            turnerBack.innerHTML = rightPage.innerHTML;
            
            // Start turn animation
            pageTurner.style.transform = "rotateY(180deg)";
            
            setTimeout(() => {
                // Reset turner
                pageTurner.style.transition = "none";
                pageTurner.style.transform = "rotateY(0)";
                pageTurner.style.zIndex = "-1";
                
                // Re-enable transition for next animation
                setTimeout(() => {
                    pageTurner.style.transition = "transform 0.6s cubic-bezier(0.645, 0.045, 0.355, 1)";
                    isTurning = false;
                }, 50);
                
                updateNavigationButtons();
            }, 600);
        }
    }
    
    // Handle window resize - now simplified
    function handleResize() {
        // Check if mobile state changed
        const wasMobile = isMobile();
        
        // Remember current page
        const currentPage = wasMobile ? (currentSpread + 1) : (currentSpread * 2 + 1);
        
        // If changing between mobile and desktop, adjust current spread
        if (wasMobile !== isMobile()) {
            // Convert current page to appropriate spread
            currentSpread = isMobile() ? (currentPage - 1) : Math.floor((currentPage - 1) / 2);
            
            // Reset layout
            toggleMobileLayout();
        }
        
        // Recalculate total spreads
        if (chapterMetadata) {
            totalSpreads = Math.ceil(chapterMetadata.totalPages / (isMobile() ? 1 : 2));
        }
        
        // Make sure we're not beyond the last spread
        if (currentSpread >= totalSpreads) {
            currentSpread = totalSpreads - 1;
        }
        
        // Update the display
        updatePageContent();
        updateNavigationButtons();
    }
    
    // Helper functions
    function getChapterIdFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const chapterId = urlParams.get('open_chapter');
        
        // Debug the chapter ID
        console.log('Chapter ID from URL:', chapterId);
        
        // Make sure we return a valid ID or a default
        return chapterId || (window.bookChapters && window.bookChapters.length > 0 ? window.bookChapters[0].id : null);
    }
    
    function showLoading() {
        // Add a loading indicator
        const loadingEl = document.createElement('div');
        loadingEl.className = 'book-loading';
        loadingEl.innerHTML = '<div class="loading-spinner"></div>';
        
        // Find where to append it
        const wrapper = document.querySelector('.book-content-wrapper');
        if (wrapper) {
            wrapper.appendChild(loadingEl);
        }
    }
    
    function hideLoading() {
        const loadingEl = document.querySelector('.book-loading');
        if (loadingEl) {
            loadingEl.remove();
        }
    }
    
    // Event listeners
    if (prevBtn) prevBtn.addEventListener('click', turnToPrevPage);
    if (nextBtn) nextBtn.addEventListener('click', turnToNextPage);
    
    // Add resize event listener with debounce
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResize, 300);
    });
    
    // Close book and return to door
    if (closeBookBtn) {
        closeBookBtn.addEventListener('click', function() {
            const doorContent = document.getElementById('doorContent');
            const magicalDoor = document.getElementById('magicalDoor');
            
            if (doorContent) {
                doorContent.classList.remove('visible');
                
                setTimeout(() => {
                    document.body.classList.remove('door-opened');
                    const titleContainer = document.querySelector('.title-container');
                    if (titleContainer) {
                        titleContainer.classList.remove('title-exit');
                    }
                    if (magicalDoor) {
                        magicalDoor.classList.remove('open');
                    }
                }, 300);
            }
        });
    }

    if (mobilePrevBtn) {
        mobilePrevBtn.addEventListener('click', turnToPrevPage);
    }
    
    if (mobileNextBtn) {
        mobileNextBtn.addEventListener('click', turnToNextPage);
    }
    
    if (mobileCloseBtn) {
        mobileCloseBtn.addEventListener('click', function() {
            const doorContent = document.getElementById('doorContent');
            const magicalDoor = document.getElementById('magicalDoor');
            
            if (doorContent) {
                doorContent.classList.remove('visible');
                
                setTimeout(() => {
                    document.body.classList.remove('door-opened');
                    const titleContainer = document.querySelector('.title-container');
                    if (titleContainer) {
                        titleContainer.classList.remove('title-exit');
                    }
                    if (magicalDoor) {
                        magicalDoor.classList.remove('open');
                    }
                }, 300);
            }
        });
    }
    
    // Add CSS for loading state
    const style = document.createElement('style');
    style.textContent = `
        .book-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 10, 30, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(138, 43, 226, 0.3);
            border-radius: 50%;
            border-top-color: rgba(138, 43, 226, 0.8);
            animation: spin 1s ease-in-out infinite;
        }
        
        .loading-page {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: rgba(138, 43, 226, 0.7);
            font-style: italic;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    // Initialize the book
    initBook();
    
    // Clean up test element when page is unloaded
    window.addEventListener('beforeunload', function() {
        if (testElement && testElement.parentNode) {
            testElement.parentNode.removeChild(testElement);
        }
    });

    let lastOrientation = window.innerHeight > window.innerWidth ? 'portrait' : 'landscape';

    window.addEventListener('resize', function() {
        // Check for orientation change
        const currentOrientation = window.innerHeight > window.innerWidth ? 'portrait' : 'landscape';
        
        if (currentOrientation !== lastOrientation) {
            // Orientation has changed, force a content reflow
            lastOrientation = currentOrientation;
            setTimeout(handleResize, 300); // Wait for resize to complete
        } else {
            // Normal resize
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(handleResize, 300);
        }
    });
});