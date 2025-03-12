<?php

namespace App\Services;

use App\Helpers\ContentFormatter;
use App\Models\Chapter;
use App\Models\ChapterPage;

class ChapterPaginationService
{
    /**
     * Paginate chapter content and store pages in the database
     *
     * @param Chapter $chapter
     * @param int $approxLinesPerPage Approximate lines per page
     * @return int Number of pages created
     */
    public function paginateChapter(Chapter $chapter, $approxLinesPerPage = 25)
    {
        // Format the content first
        $formattedContent = ContentFormatter::format($chapter->content);
        
        // Clear existing pages
        $chapter->pages()->delete();
        
        // Split content into paragraphs (respecting HTML tags)
        $paragraphs = $this->splitContentIntoParagraphs($formattedContent);
        
        $pages = [];
        $currentPage = [];
        $currentLines = 0;
        $pageNumber = 1;
        
        // Group paragraphs into pages
        foreach ($paragraphs as $paragraph) {
            // Check if paragraph contains a large image that should be placed on its own page
            $hasLargeImage = $this->containsLargeImage($paragraph);
            
            // Estimate lines for this paragraph (considering average line length and HTML tags)
            $estimatedLines = $this->estimateParagraphLines($paragraph);
            
            // Handle large images or exceptionally long paragraphs
            if (($hasLargeImage || $estimatedLines > $approxLinesPerPage * 0.8) && count($currentPage) > 0) {
                // Save current page before this large content
                $this->savePage($chapter, $pageNumber, $currentPage);
                $pageNumber++;
                
                // Reset for next page
                $currentPage = [];
                $currentLines = 0;
            }
            
            // If adding this paragraph would exceed the page limit, start a new page
            // But avoid creating pages with only a single line at the top
            if ($currentLines > 0 && ($currentLines + $estimatedLines) > $approxLinesPerPage) {
                // Avoid orphaned paragraphs (single line paragraphs at the end of a page)
                $lastParagraph = end($currentPage);
                $lastParagraphLines = $this->estimateParagraphLines($lastParagraph);
                
                // If the last paragraph is very short (1-2 lines) and not the only paragraph, 
                // move it to the next page instead
                if ($lastParagraphLines <= 2 && count($currentPage) > 1 && 
                   ($currentLines - $lastParagraphLines + $estimatedLines) <= $approxLinesPerPage) {
                    // Remove the last paragraph from current page
                    array_pop($currentPage);
                    $currentLines -= $lastParagraphLines;
                    
                    // Save the current page
                    $this->savePage($chapter, $pageNumber, $currentPage);
                    $pageNumber++;
                    
                    // Start next page with the removed paragraph and the new one
                    $currentPage = [$lastParagraph, $paragraph];
                    $currentLines = $lastParagraphLines + $estimatedLines;
                } else {
                    // Regular page break
                    $this->savePage($chapter, $pageNumber, $currentPage);
                    $pageNumber++;
                    
                    // Reset for next page
                    $currentPage = [$paragraph];
                    $currentLines = $estimatedLines;
                }
            } else {
                // Add paragraph to current page
                $currentPage[] = $paragraph;
                $currentLines += $estimatedLines;
            }
            
            // If this paragraph had a large image, and we're near the page capacity,
            // force a page break after it to avoid awkward content flow
            if ($hasLargeImage && $currentLines > ($approxLinesPerPage * 0.7)) {
                // Save the current page
                $this->savePage($chapter, $pageNumber, $currentPage);
                $pageNumber++;
                
                // Reset for next page
                $currentPage = [];
                $currentLines = 0;
            }
        }
        
        // Save the last page if it has content
        if (count($currentPage) > 0) {
            $this->savePage($chapter, $pageNumber, $currentPage);
        }
        
        return $pageNumber;
    }

    /**
     * Check if paragraph contains a large image that should be handled specially
     *
     * @param string $paragraph HTML content
     * @return bool
     */
    protected function containsLargeImage($paragraph)
    {
        // Check for images with large dimensions
        if (preg_match_all('/<img[^>]*>/i', $paragraph, $imgMatches)) {
            foreach ($imgMatches[0] as $img) {
                // Check height attribute
                if (preg_match('/height\s*=\s*["\']?(\d+)["\']?/i', $img, $heightMatch)) {
                    $imageHeight = intval($heightMatch[1]);
                    if ($imageHeight > 200) { // Consider images taller than 200px as "large"
                        return true;
                    }
                }
                
                // Check width attribute
                if (preg_match('/width\s*=\s*["\']?(\d+)["\']?/i', $img, $widthMatch)) {
                    $imageWidth = intval($widthMatch[1]);
                    if ($imageWidth > 400) { // Consider images wider than 400px as "large"
                        return true;
                    }
                }
                
                // Check for CSS classes that might indicate large images
                if (preg_match('/class\s*=\s*["\'][^"\']*(?:large|full|wide)[^"\']*["\']/i', $img)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Estimate the number of lines a paragraph will take when rendered
     * 
     * @param string $paragraph HTML paragraph content
     * @param int $avgCharsPerLine Average characters per line (adjusted for HTML)
     * @return int Estimated number of lines
     */
    protected function estimateParagraphLines($paragraph, $avgCharsPerLine = 60)
    {
        // Strip HTML tags to get just the text content
        $textContent = strip_tags($paragraph);
        $charCount = strlen($textContent);
        
        // Count explicit line breaks in the HTML
        $brCount = substr_count(strtolower($paragraph), '<br');
        
        // Count other block elements that would cause line breaks
        $blockElementCount = preg_match_all('/<(p|div|h[1-6]|ul|ol|li|table|blockquote)[^>]*>/i', $paragraph, $matches);
        
        // Check for images and estimate their line height
        $imageLines = 0;
        if (preg_match_all('/<img[^>]*>/i', $paragraph, $imgMatches)) {
            foreach ($imgMatches[0] as $img) {
                // Try to get image height from attributes
                if (preg_match('/height\s*=\s*["\']?(\d+)["\']?/i', $img, $heightMatch)) {
                    // Estimate lines based on image height (assuming ~20px per line)
                    $imageHeight = intval($heightMatch[1]);
                    $imageLines += max(ceil($imageHeight / 20), 5); // Minimum 5 lines for any image
                } else {
                    // Default height for images without specified height
                    $imageLines += 10; // Assume an average image takes about 10 lines
                }
                
                // Add a line break after each image
                $imageLines += 1;
            }
        }
        
        // Basic line calculation based on character count
        $textLines = ceil($charCount / $avgCharsPerLine);
        
        // Add extra lines for HTML elements that cause breaks
        $extraLines = $brCount + $blockElementCount;
        
        // Additional lines for formatting elements that might cause wrapping
        $formattingElements = substr_count(strtolower($paragraph), '<strong') + 
                             substr_count(strtolower($paragraph), '<em') +
                             substr_count(strtolower($paragraph), '<span');
        $formattingAdjustment = ceil($formattingElements / 10); // Assume every 10 formatting elements might cause one extra line
        
        // Calculate total estimated lines with padding for safety
        $totalEstimatedLines = $textLines + $extraLines + $formattingAdjustment + $imageLines;
        
        // Every paragraph should have at least one line
        return max(1, $totalEstimatedLines);
    }
    
    /**
     * Split content into paragraphs while preserving HTML
     *
     * @param string $content
     * @return array
     */
    protected function splitContentIntoParagraphs($content)
    {
        // Split by paragraph tags or line breaks
        $pattern = '/<\/p>\s*<p>|<br\s*\/?>\s*<br\s*\/?>/';
        $paragraphs = preg_split($pattern, $content);
        
        // Clean up paragraphs and ensure they have paragraph tags
        return array_map(function($p) {
            $p = trim($p);
            if (!preg_match('/^<p>/i', $p)) {
                $p = '<p>' . $p;
            }
            if (!preg_match('/<\/p>$/i', $p)) {
                $p = $p . '</p>';
            }
            return $p;
        }, $paragraphs);
    }
    
    /**
     * Save a page to the database
     *
     * @param Chapter $chapter
     * @param int $pageNumber
     * @param array $paragraphs
     * @return ChapterPage
     */
    protected function savePage(Chapter $chapter, $pageNumber, array $paragraphs)
    {
        $content = implode("\n", $paragraphs);
        
        return ChapterPage::create([
            'chapter_id' => $chapter->id,
            'page_number' => $pageNumber,
            'content' => $content, // Raw content
            'formatted_content' => $content // Already formatted
        ]);
    }
}