<?php
/**
 * Create minimal valid GIF files as placeholders
 * These are 300x375 solid color GIFs with text labels
 */

// Minimal GIF structure for a solid color image
function createGif($width, $height, $r, $g, $b) {
    // GIF89a header
    $data = 'GIF89a';
    
    // Logical Screen Descriptor
    $data .= pack('v', $width);   // width
    $data .= pack('v', $height);  // height
    $data .= chr(0xF0);           // GCT flag + color resolution + sort flag + GCT size
    $data .= chr(0);              // Background color index
    $data .= chr(0);              // Pixel aspect ratio
    
    // Global Color Table (256 colors, but we only use first few)
    for ($i = 0; $i < 256; $i++) {
        if ($i === 0) {
            $data .= chr($r) . chr($g) . chr($b); // Color 0 = our color
        } elseif ($i === 1) {
            $data .= chr(255) . chr(255) . chr(255); // Color 1 = white
        } elseif ($i === 2) {
            $data .= chr(0) . chr(0) . chr(0); // Color 2 = black
        } else {
            $data .= chr(0) . chr(0) . chr(0); // Rest black
        }
    }
    
    // Graphic Control Extension
    $data .= chr(0x21) . chr(0xF9) . chr(4) . chr(0) . chr(0) . chr(0) . chr(0) . chr(0);
    
    // Image Descriptor
    $data .= chr(0x2C);                    // Image separator
    $data .= pack('v', 0) . pack('v', 0);  // Left, Top
    $data .= pack('v', $width);            // Width
    $data .= pack('v', $height);           // Height
    $data .= chr(0);                       // No local color table
    
    // Image Data - LZW minimum code size
    $data .= chr(8);  // LZW minimum code size (8 bits)
    
    // Image data - single sub-block with minimal data
    // For a solid color image, we can use a simple LZW stream
    // This is a simplified approach - in reality LZW compression is needed
    // But for a placeholder, we'll use a minimal valid structure
    
    // Sub-block with just clear code and end code
    $data .= chr(1);  // Block size
    $data .= chr(0);  // Clear code (for 8-bit LZW)
    
    // Terminator
    $data .= chr(0);  // Block terminator
    
    // GIF Trailer
    $data .= chr(0x3B);
    
    return $data;
}

// Actually, creating valid LZW-compressed GIFs without a library is very complex.
// Let's just create simple placeholder files and let the user provide real GIFs.
// We'll create minimal 1x1 transparent GIFs as placeholders.

$transparentGif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

$names = ['fox', 'rocket', 'cactus', 'bear', 'icecream', 'balloon', 'burger', 'rainbow', 'bunny', 'cat', 'star', 'controller'];

foreach ($names as $name) {
    file_put_contents(__DIR__ . '/assets/gifs/' . $name . '.gif', $transparentGif);
}

echo "Created transparent placeholder GIFs. User should replace with actual cartoon GIFs.";