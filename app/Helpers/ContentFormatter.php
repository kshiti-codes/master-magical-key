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
        $content = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $content);
        $content = preg_replace('/_(.*?)_/s', '<em>$1</em>', $content);
        
        // Format quotes (> style)
        $content = preg_replace('/^&gt;\s*(.*?)$/m', '<blockquote>$1</blockquote>', $content);
        
        // Format headers
        $content = preg_replace('/^#{6}\s+(.*?)$/m', '<h6>$1</h6>', $content);
        $content = preg_replace('/^#{5}\s+(.*?)$/m', '<h5>$1</h5>', $content);
        $content = preg_replace('/^#{4}\s+(.*?)$/m', '<h4>$1</h4>', $content);
        $content = preg_replace('/^#{3}\s+(.*?)$/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^#{2}\s+(.*?)$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^#{1}\s+(.*?)$/m', '<h1>$1</h1>', $content);
        
        return $content;
    }
}