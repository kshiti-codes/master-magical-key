<?php

namespace App\Helpers;

class ContentFormatter
{
    /**
     * Format the chapter content with proper styling
     *
     * @param string $content The raw content with markdown-style formatting
     * @return string HTML formatted content
     */
    public static function format($content)
    {
        // Replace paragraph breaks
        $content = nl2br($content);
        
        // Format images with markdown syntax: ![alt text](image_url) or ![alt text](image_url|width=300|height=200)
        $content = preg_replace_callback('/!\[(.*?)\]\((.*?)\)/s', function($matches) {
            $alt = $matches[1];
            $src = $matches[2];
            
            // Check if there are any parameters
            $width = '';
            $height = '';
            $class = 'chapter-image'; // Default class
            
            if (strpos($src, '|') !== false) {
                $parts = explode('|', $src);
                $src = $parts[0];
                
                // Parse parameters
                foreach (array_slice($parts, 1) as $param) {
                    if (preg_match('/width=(\d+)/', $param, $widthMatch)) {
                        $width = ' width="' . $widthMatch[1] . '"';
                    }
                    if (preg_match('/height=(\d+)/', $param, $heightMatch)) {
                        $height = ' height="' . $heightMatch[1] . '"';
                    }
                    if (preg_match('/class=([\w-]+)/', $param, $classMatch)) {
                        $class .= ' ' . $classMatch[1];
                    }
                }
            }
            
            return '<img src="' . $src . '" alt="' . $alt . '"' . $width . $height . ' class="' . $class . '">';
        }, $content);
        
        // Format bold text (handle both ** and __ style)
        $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
        $content = preg_replace('/__(.*?)__/s', '<strong>$1</strong>', $content);
        
        // Format italic text (handle both * and _ style)
        $content = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $content);
        $content = preg_replace('/(?<!_)_(?!_)(.*?)(?<!_)_(?!_)/s', '<em>$1</em>', $content);
        
        // Format quotes (> style)
        $content = preg_replace('/^&gt;\s*(.*?)$/m', '<blockquote>$1</blockquote>', $content);
        
        // Format headers
        $content = preg_replace('/^#{6}\s+(.*?)$/m', '<h6>$1</h6>', $content);
        $content = preg_replace('/^#{5}\s+(.*?)$/m', '<h5>$1</h5>', $content);
        $content = preg_replace('/^#{4}\s+(.*?)$/m', '<h4>$1</h4>', $content);
        $content = preg_replace('/^#{3}\s+(.*?)$/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^#{2}\s+(.*?)$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^#{1}\s+(.*?)$/m', '<h1>$1</h1>', $content);
        
        // Format unordered lists
        $content = preg_replace_callback('/(?:^|\n)(\*|\-|\+)\s+(.*?)(?=\n\n|\n(?:\*|\-|\+)|\z)/s', function($matches) {
            $items = preg_split('/\n\s*(?:\*|\-|\+)\s+/', "\n" . $matches[0]);
            array_shift($items); // Remove first empty item
            
            $html = '<ul>';
            foreach ($items as $item) {
                $html .= '<li>' . trim($item) . '</li>';
            }
            $html .= '</ul>';
            
            return $html;
        }, $content);
        
        // Format ordered lists
        $content = preg_replace_callback('/(?:^|\n)(\d+)\.\s+(.*?)(?=\n\n|\n\d+\.|\z)/s', function($matches) {
            $items = preg_split('/\n\s*\d+\.\s+/', "\n" . $matches[0]);
            array_shift($items); // Remove first empty item
            
            $html = '<ol>';
            foreach ($items as $item) {
                $html .= '<li>' . trim($item) . '</li>';
            }
            $html .= '</ol>';
            
            return $html;
        }, $content);
        
        // Format code blocks
        $content = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $content);
        
        // Format inline code
        $content = preg_replace('/`(.*?)`/s', '<code>$1</code>', $content);
        
        // Format horizontal rules
        $content = preg_replace('/^(\-{3,}|\*{3,}|_{3,})$/m', '<hr>', $content);
        
        // Ensure proper paragraph tags
        // First, identify blocks that shouldn't be wrapped (headers, lists, etc.)
        $nonParagraphBlocks = [
            '<h1', '<h2', '<h3', '<h4', '<h5', '<h6',
            '<ul', '<ol', '<li', '<blockquote', '<pre',
            '<hr', '<p'
        ];
        
        // Split content by double line breaks
        $blocks = preg_split('/\n\s*\n/', $content);
        
        // Process each block
        $processedContent = '';
        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block)) continue;
            
            // Check if this block starts with a tag that shouldn't be wrapped
            $shouldWrap = true;
            foreach ($nonParagraphBlocks as $tag) {
                if (strpos($block, $tag) === 0) {
                    $shouldWrap = false;
                    break;
                }
            }
            
            // Wrap in paragraph tags if needed
            if ($shouldWrap && !preg_match('/^<p/i', $block)) {
                $block = '<p>' . $block . '</p>';
            }
            
            $processedContent .= $block . "\n\n";
        }
        
        return trim($processedContent);
    }
}