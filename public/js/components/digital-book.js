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

    window.findChapterPage = function(chapterId) {
        if (!window.pageToChapterMap) return null;
        
        // Convert to number for consistent comparison
        chapterId = parseInt(chapterId);
        
        for (let pageIndex in window.pageToChapterMap) {
            if (parseInt(window.pageToChapterMap[pageIndex]) === chapterId) {
                return parseInt(pageIndex);
            }
        }
        
        return null;
    };

    window.openSpecificChapter = function(chapterId) {
        if (!bookInitialized) {
            console.error('Book not initialized yet');
            return false;
        }
        // Safety check
        if (!bookInitialized || !chapterId) return false;
        // Find the page using our map
        const pageIndex = window.findChapterPage(chapterId);

        if (pageIndex !== null) {
            if (isMobile()) {
                // In mobile, just go to the page
                currentSpread = pageIndex;
            } else {
                // In desktop, calculate the spread
                currentSpread = Math.floor(pageIndex / 2);
            }
            
            // Update the content
            updatePageContent();
            updateNavigationButtons();
            return true;
        }
        
        // Convert to string for consistent matching
        chapterId = String(chapterId);
        
        // Look through all pages to find the one containing this chapter
        let targetSpread = null;
        
        if (isMobile()) {
            // In mobile view, find the page directly
            for (let i = 0; i < bookPages.length; i++) {
                if (bookPages[i].includes(`Chapter ${chapterId}`) || 
                    bookPages[i].includes(`Chapter ${chapterId}:`)) {
                    targetSpread = i;
                    break;
                }
            }
        } else {
            // In desktop view, find the spread
            for (let i = 0; i < Math.ceil(bookPages.length / 2); i++) {
                const leftPageIdx = i * 2;
                const rightPageIdx = leftPageIdx + 1;
                
                // Check left page
                if (leftPageIdx < bookPages.length && 
                    (bookPages[leftPageIdx].includes(`Chapter ${chapterId}`) || 
                     bookPages[leftPageIdx].includes(`Chapter ${chapterId}:`))) {
                    targetSpread = i;
                    break;
                }
                
                // Check right page
                if (rightPageIdx < bookPages.length && 
                    (bookPages[rightPageIdx].includes(`Chapter ${chapterId}`) || 
                     bookPages[rightPageIdx].includes(`Chapter ${chapterId}:`))) {
                    targetSpread = i;
                    break;
                }
            }
        }
        
        // If we found the spread, navigate to it
        if (targetSpread !== null) {
            currentSpread = targetSpread;
            updatePageContent();
            updateNavigationButtons();
            return true;
        }
        
        return false;
    };

    // Process content into pages based on available space
    function processContentIntoPages() {
        bookPages = [];
        window.pageToChapterMap = {}; // Reset the mapping
        
        // Process introduction
        const introTitle = `<h2 class='chapter-title'>${rawChapterContent.introduction.title}</h2>`;
        const introParagraphs = rawChapterContent.introduction.content.split('\n\n');
        
        // Create introduction pages by measuring actual rendered height
        let currentIntroContent = introTitle;
        let remainingIntroParagraphs = [...introParagraphs];
        
        // Process intro paragraphs until they're all used
        while (remainingIntroParagraphs.length > 0) {
            let currentHeight = 0;
            let paragraphsForThisPage = [];
            
            // Measure title height if this is the first page
            if (currentIntroContent === introTitle) {
                testElement.innerHTML = introTitle;
                currentHeight += testElement.offsetHeight;
            }

            // Mobile - add more paragraphs per page
            const heightLimit = getPageContentHeight();
            
            // Add paragraphs until we run out of space
            while (remainingIntroParagraphs.length > 0) {
                const nextParagraph = `<p>${remainingIntroParagraphs[0]}</p>`;
                testElement.innerHTML = nextParagraph;
                const paragraphHeight = testElement.offsetHeight;
                
                const fillRatio = isMobile() ? 0.95 : 0.9;
                // Check if adding this paragraph would exceed page height
                if (currentHeight + paragraphHeight < heightLimit * fillRatio) {
                    paragraphsForThisPage.push(nextParagraph);
                    currentHeight += paragraphHeight;
                    remainingIntroParagraphs.shift(); // Remove this paragraph from remaining
                } else {
                    break; // Page is full
                }
            }
            
            // Create the page with collected paragraphs
            bookPages.push(currentIntroContent + paragraphsForThisPage.join(''));
            
            // Reset for next page
            currentIntroContent = '';
        }
        
        // Process each chapter
        Object.keys(rawChapterContent).forEach(key => {
            if (key === 'introduction') return; // Skip intro, already processed
            
            const chapter = rawChapterContent[key];
            
            if (chapter.isPurchased) {
                // For purchased chapters, format the content and split into pages
                const chapterTitle = `<h2 class='chapter-title'>Chapter ${chapter.id}</h2>`;
                const chapterDescription = `<h3 class='chapter-description'>${chapter.title}</h3>`;
                const header = chapterTitle + chapterDescription;
                
                // Split the content into paragraphs
                const paragraphs = chapter.content.split('\n\n').map(p => `<p>${p}</p>`);
                
                // Create first chapter page
                let currentContent = header;
                let remainingParagraphs = [...paragraphs];
                let pageCount = 0;
                
                // Process paragraphs until they're all used
                while (remainingParagraphs.length > 0) {
                    let currentHeight = 0;
                    let paragraphsForThisPage = [];
                    
                    // Measure header height if this is the first page
                    if (pageCount === 0) {
                        testElement.innerHTML = header;
                        currentHeight += testElement.offsetHeight;
                    } else {
                        // For continuation pages, add a subtitle
                        const continueHeader = ``;
                        testElement.innerHTML = continueHeader;
                        currentHeight += testElement.offsetHeight;
                        currentContent = continueHeader;
                    }
                    
                    // Add paragraphs until we run out of space
                    while (remainingParagraphs.length > 0) {
                        testElement.innerHTML = remainingParagraphs[0];
                        const paragraphHeight = testElement.offsetHeight;
                        
                        // Check if adding this paragraph would exceed page height
                        if (currentHeight + paragraphHeight < getPageContentHeight()) {
                            paragraphsForThisPage.push(remainingParagraphs[0]);
                            currentHeight += paragraphHeight;
                            remainingParagraphs.shift(); // Remove this paragraph from remaining
                        } else {
                            break; // Page is full
                        }
                    }
                    
                    // Wrap the content in a container div
                    const pageContent = `
                        <div class="chapter-content ${pageCount > 0 ? 'chapter-content-continued' : ''}">
                            ${currentContent}
                            <div class="chapter-text">
                                ${paragraphsForThisPage.join('')}
                            </div>
                        </div>
                    `;
                    
                    // Add the page
                    bookPages.push(pageContent);
                    
                    // Store the first page of this chapter in our mapping
                    if (pageCount === 0) {
                        window.pageToChapterMap[bookPages.length - 1] = chapter.id;
                    }
                    
                    pageCount++;
                }
            } else {
                // For locked chapters, create a single page with purchase option
                const lockedPageContent = `
                    <div class="chapter-content">
                        <h2 class="chapter-title">Chapter ${chapter.id}</h2>
                        <h3 class="chapter-description">${chapter.title}</h3>
                        <div class="chapter-purchase">
                            <p>${chapter.content || 'This chapter contains sacred wisdom about the universe and your connection to it.'}</p>
                            <p class="chapter-price">$${parseFloat(chapter.price).toFixed(2)} AUD</p>
                            <a href="${chapter.purchaseUrl}" class="btn btn-portal">Purchase Chapter</a>
                        </div>
                    </div>
                `;
                
                bookPages.push(lockedPageContent);
                
                // Store this chapter in our mapping
                window.pageToChapterMap[bookPages.length - 1] = chapter.id;
            }
        });
        
        // Add "coming soon" page at the end
        bookPages.push(`
            <div class="coming-soon">
                <h2 class="chapter-title">More Chapters Coming Soon</h2>
                <div class="coming-soon-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <p>The journey continues with new chapters being prepared for your enlightenment.</p>
                <p>Return soon to discover more cosmic wisdom and revelations.</p>
            </div>
        `);
    }
    
    // Book content
    let bookPages = [];
    let rawChapterContent = {};
    
    // Introduction pages
    const introductionContent = [
        "<h2 class='chapter-title'>The Master Magical Key to the Universe</h2>" +
        "<p>Welcome to an extraordinary journey of cosmic wisdom and enlightenment.</p>" +
        "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl.</p>",
        
        "<p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>" +
        "<p>Begin your journey by turning the page to explore the chapters...</p>"
    ];
    
    // Current state
    let currentSpread = 0; // The current opened spread (pair of pages)
    let totalSpreads = 0;  // Total number of spreads in the book
    let isTurning = false; // Flag to prevent multiple turning animations
    
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
    
    // Store raw chapter content first
    function storeRawContent() {
        // Store introduction
        rawChapterContent.introduction = {
            title: "The Master Magical Key to the Universe",
            content: "Welcome to an extraordinary journey of cosmic wisdom and enlightenment.\n\n" +
                    "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl.\n\n" +
                    "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n"
        };
        
        // Store chapters
        if (window.bookChapters && window.bookChapters.length > 0) {
            window.bookChapters.forEach(chapter => {
                rawChapterContent[`chapter_${chapter.id}`] = {
                    id: chapter.id,
                    title: chapter.title,
                    description: chapter.description || 'Journey through cosmic wisdom',
                    content: chapter.isPurchased ? (chapter.fullContent || chapter.previewContent) : chapter.previewContent,
                    isPurchased: chapter.isPurchased,
                    price: chapter.price,
                    purchaseUrl: chapter.purchaseUrl,
                    readUrl: chapter.readUrl
                };
            });
        }
        window.pageToChapterMap = {};
    }

    // Add this function to detect mobile devices
    function isMobile() {
        return window.innerWidth <= 767;
    }
    
    // Process content into pages based on available space
    function processContentIntoPages() {
        bookPages = [];
        
        // Process introduction
        const introTitle = `<h2 class='chapter-title'>${rawChapterContent.introduction.title}</h2>`;
        const introParagraphs = rawChapterContent.introduction.content.split('\n\n');
        
        // Create introduction pages by measuring actual rendered height
        let currentIntroContent = introTitle;
        let remainingIntroParagraphs = [...introParagraphs];
        
        // Process intro paragraphs until they're all used
        while (remainingIntroParagraphs.length > 0) {
            let currentHeight = 0;
            let paragraphsForThisPage = [];
            
            // Measure title height if this is the first page
            if (currentIntroContent === introTitle) {
                testElement.innerHTML = introTitle;
                currentHeight += testElement.offsetHeight;
            }

            // Mobile - add more paragraphs per page
            const heightLimit = getPageContentHeight();
            
            // Add paragraphs until we run out of space
            while (remainingIntroParagraphs.length > 0) {
                const nextParagraph = `<p>${remainingIntroParagraphs[0]}</p>`;
                testElement.innerHTML = nextParagraph;
                const paragraphHeight = testElement.offsetHeight;
                
                const fillRatio = isMobile() ? 0.95 : 0.9;
                // Check if adding this paragraph would exceed page height
                if (currentHeight + paragraphHeight < heightLimit * fillRatio) {
                    paragraphsForThisPage.push(nextParagraph);
                    currentHeight += paragraphHeight;
                    remainingIntroParagraphs.shift(); // Remove this paragraph from remaining
                } else {
                    break; // Page is full
                }
            }
            
            // Create the page with collected paragraphs
            bookPages.push(currentIntroContent + paragraphsForThisPage.join(''));
            
            // Reset for next page
            currentIntroContent = '';
        }
        
        // Process each chapter
        Object.keys(rawChapterContent).forEach(key => {
            if (key === 'introduction') return; // Skip intro, already processed
            
            const chapter = rawChapterContent[key];
            
            if (chapter.isPurchased) {
                // For purchased chapters, format the content and split into pages
                const chapterTitle = `<h2 class='chapter-title'>Chapter ${chapter.id}</h2>`;
                const chapterDescription = `<h3 class='chapter-description'>${chapter.title}</h3>`;
                const header = chapterTitle + chapterDescription;
                
                // Split the content into paragraphs
                const paragraphs = chapter.content.split('\n\n').map(p => `<p>${p}</p>`);
                
                // Create first chapter page
                let currentContent = header;
                let remainingParagraphs = [...paragraphs];
                let pageCount = 0;
                
                // Process paragraphs until they're all used
                while (remainingParagraphs.length > 0) {
                    let currentHeight = 0;
                    let paragraphsForThisPage = [];
                    
                    // Measure header height if this is the first page
                    if (pageCount === 0) {
                        testElement.innerHTML = header;
                        currentHeight += testElement.offsetHeight;
                    } else {
                        // For continuation pages, add a subtitle
                        const continueHeader = ``;
                        testElement.innerHTML = continueHeader;
                        currentHeight += testElement.offsetHeight;
                        currentContent = continueHeader;
                    }
                    
                    // Add paragraphs until we run out of space
                    while (remainingParagraphs.length > 0) {
                        testElement.innerHTML = remainingParagraphs[0];
                        const paragraphHeight = testElement.offsetHeight;
                        
                        // Check if adding this paragraph would exceed page height
                        if (currentHeight + paragraphHeight < getPageContentHeight()) {
                            paragraphsForThisPage.push(remainingParagraphs[0]);
                            currentHeight += paragraphHeight;
                            remainingParagraphs.shift(); // Remove this paragraph from remaining
                        } else {
                            break; // Page is full
                        }
                    }
                    
                    // Wrap the content in a container div
                    const pageContent = `
                        <div class="chapter-content ${pageCount > 0 ? 'chapter-content-continued' : ''}">
                            ${currentContent}
                            <div class="chapter-text">
                                ${paragraphsForThisPage.join('')}
                            </div>
                        </div>
                    `;
                    
                    // Add the page
                    bookPages.push(pageContent);
                    pageCount++;
                }
            } else {
                // For locked chapters, create a single page with purchase option
                const lockedPageContent = `
                    <div class="chapter-content">
                        <h2 class="chapter-title">Chapter ${chapter.id}</h2>
                        <h3 class="chapter-description">${chapter.title}</h3>
                        <div class="chapter-purchase">
                            <p>${chapter.content || 'This chapter contains sacred wisdom about the universe and your connection to it.'}</p>
                            <p class="chapter-price">$${parseFloat(chapter.price).toFixed(2)} AUD</p>
                            <a href="${chapter.purchaseUrl}" class="btn btn-portal">Purchase Chapter</a>
                        </div>
                    </div>
                `;
                
                bookPages.push(lockedPageContent);
            }
        });
        
        // Add "coming soon" page at the end
        bookPages.push(`
            <div class="coming-soon">
                <h2 class="chapter-title">More Chapters Coming Soon</h2>
                <div class="coming-soon-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <p>The journey continues with new chapters being prepared for your enlightenment.</p>
                <p>Return soon to discover more cosmic wisdom and revelations.</p>
            </div>
        `);
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
    
    // Create decorative page lines
    function createPageLines() {
        let lines = '<div class="page-lines">';
        for (let i = 1; i <= 9; i++) {
            lines += '<div class="page-line"></div>';
        }
        lines += '</div>';
        return lines;
    }
    
    // Initialize book
    function initBook() {
        // Store raw content first
        storeRawContent();
        
        // Process content into pages
        processContentIntoPages();
        
        // Calculate total spreads (pairs of pages)
        totalSpreads = Math.ceil(bookPages.length / 2);
        
        // Set initial content
        updatePageContent();
        updateNavigationButtons();

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
        // Make bookPages accessible
        window.bookPagesContent = bookPages;
        bookInitialized = true;
        window.bookInitialized = bookInitialized;
    }
    
    // Update page content based on current spread
    function updatePageContent() {
        if (isMobile()) {
            // Mobile - show only the current page
            const pageNum = currentSpread + 1;
            
            // Update content in left page only
            if (pageNum <= bookPages.length) {
                leftPage.innerHTML = bookPages[pageNum - 1] + 
                    createPageLines();
            } else {
                leftPage.innerHTML = createPageLines();
            }
            
            // Update mobile page indicator
            const pageIndicator = document.getElementById('mobilePageIndicator');
            if (pageIndicator) {
                pageIndicator.textContent = `Page ${pageNum}/${bookPages.length}`;
            }
        } else {
            // Desktop - show spread of two pages
            const leftPageNum = currentSpread * 2 + 1;
            const rightPageNum = leftPageNum + 1;
            
            // Left page content
            if (leftPageNum <= bookPages.length) {
                leftPage.innerHTML = bookPages[leftPageNum - 1] + 
                    `<div class="page-number page-number-left">${leftPageNum}</div>` +
                    createPageLines();
            } else {
                leftPage.innerHTML = createPageLines() + 
                    `<div class="page-number page-number-left">${leftPageNum}</div>`;
            }
            
            // Right page content
            if (rightPageNum <= bookPages.length) {
                rightPage.innerHTML = bookPages[rightPageNum - 1] + 
                    `<div class="page-number page-number-right">${rightPageNum}</div>` +
                    createPageLines();
            } else {
                rightPage.innerHTML = createPageLines() + 
                    `<div class="page-number page-number-right">${rightPageNum}</div>`;
            }
        }
    }
    
    // Update navigation buttons state
    function updateNavigationButtons() {
        // In mobile view, adjust based on total pages rather than spreads
        if (isMobile()) {
            const currentPage = currentSpread + 1;
            const totalPages = bookPages.length;
            
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
                const maxSpread = Math.ceil(bookPages.length / 2) - 1;
                nextBtn.style.opacity = currentSpread < maxSpread ? "1" : "0.3";
                nextBtn.style.pointerEvents = currentSpread < maxSpread ? "auto" : "none";
            }
        }
    }

    //function to toggle between desktop and mobile layouts
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
            
            // Adjust the book pages array for single page view
            reorganizeContentForMobile();
            
            // Update navigation display
            updateMobilePageIndicator();
        } else {
            // Switch to desktop layout
            digitalBook.classList.remove('mobile-view');
            bookContainer.classList.remove('mobile-view');
            bookContentWrapper.classList.remove('mobile-view');
            
            // Reset to original desktop pages
            processContentIntoPages();
        }
        
        // Update the visible content
        updatePageContent();
    }

    // Reorganize content for mobile view (single page instead of spread)
    function reorganizeContentForMobile() {
        // Store original pages
        const originalPages = [...bookPages];
        bookPages = [];
        
        // Just use each page individually for mobile
        originalPages.forEach(page => {
            bookPages.push(page);
        });
        
        // Recalculate total spreads for mobile (each spread is just 1 page)
        totalSpreads = bookPages.length;
    }

    // Update mobile page indicator
    function updateMobilePageIndicator() {
        const pageIndicator = document.getElementById('mobilePageIndicator');
        if (pageIndicator) {
            const currentPage = currentSpread + 1;
            const totalPages = totalSpreads;
            pageIndicator.textContent = `Page ${currentPage}/${totalPages}`;
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
    
    // Handle window resize
    function handleResize() {
        // Reprocess pages based on new size
        processContentIntoPages();
        
        // Remember current page
        const currentPage = currentSpread * 2 + 1;
        
        // Recalculate total spreads
        totalSpreads = Math.ceil(bookPages.length / 2);
        
        // Try to go to same content (approximately)
        currentSpread = Math.floor((currentPage - 1) / 2);
        
        // Make sure we're not beyond the last spread
        if (currentSpread >= totalSpreads) {
            currentSpread = totalSpreads - 1;
        }

        // Toggle between mobile and desktop layouts
        toggleMobileLayout();
        
        // Update page content
        updatePageContent();
        updateNavigationButtons();
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