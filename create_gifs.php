<?php
/**
 * Create animated GIFs for intro animation
 * Generates simple animated GIFs for each character
 */

$gifDir = __DIR__ . '/assets/gifs/';
$chars = [
    'fox' => ['color' => [255, 107, 53], 'name' => 'Fox'],
    'rocket' => ['color' => [255, 213, 103], 'name' => 'Rocket'],
    'cactus' => ['color' => [76, 175, 80], 'name' => 'Cactus'],
    'bear' => ['color' => [121, 85, 72], 'name' => 'Bear'],
    'icecream' => ['color' => [255, 182, 193], 'name' => 'Ice Cream'],
    'balloon' => ['color' => [255, 107, 53], 'name' => 'Balloon'],
    'burger' => ['color' => [255, 193, 7], 'name' => 'Burger'],
    'rainbow' => ['color' => [255, 0, 0], 'name' => 'Rainbow', 'rainbow' => true],
    'bunny' => ['color' => [255, 182, 193], 'name' => 'Bunny'],
    'cat' => ['color' => [255, 160, 122], 'name' => 'Cat'],
    'star' => ['color' => [255, 215, 0], 'name' => 'Star'],
    'controller' => ['color' => [138, 43, 226], 'name' => 'Controller'],
];

foreach ($chars as $name => $data) {
    createAnimatedGif($gifDir . $name . '.gif', $data);
    echo "Created: $name.gif\n";
}

function createAnimatedGif($filename, $data) {
    $frames = 20;
    $width = 200;
    $height = 250;
    $delay = 10; // centiseconds
    
    $color = $data['color'];
    $isRainbow = isset($data['rainbow']) && $data['rainbow'];
    
    $im = imagecreatetruecolor($width, $height);
    
    // Enable transparency
    imagealphablending($im, false);
    imagesavealpha($im, true);
    
    $framesData = [];
    
    for ($frame = 0; $frame < $frames; $frame++) {
        $frameIm = imagecreatetruecolor($width, $height);
        imagealphablending($frameIm, false);
        imagesavealpha($frameIm, true);
        
        // Clear with transparent background
        $transparent = imagecolorallocatealpha($frameIm, 0, 0, 0, 127);
        imagefill($frameIm, 0, 0, $transparent);
        
        if ($isRainbow) {
            // Rainbow animation - rotating colors
            $hue = ($frame / $frames) * 360;
            drawRainbowCharacter($frameIm, $width, $height, $hue);
        } else {
            // Simple character with bounce animation
            drawCharacter($frameIm, $width, $height, $color, $frame, $frames, $data['name']);
        }
        
        // Save frame to buffer
        ob_start();
        imagegif($frameIm);
        $framesData[] = ob_get_clean();
        imagedestroy($frameIm);
    }
    
    // Write animated GIF
    $fp = fopen($filename, 'wb');
    if ($fp) {
        // Write first frame
        fwrite($fp, $framesData[0]);
        
        // Write subsequent frames with Graphic Control Extension
        for ($i = 1; $i < count($framesData); $i++) {
            fwrite($fp, chr(0x21) . chr(0xF9) . chr(0x04) . chr(0x01) . chr($delay & 0xFF) . chr(($delay >> 8) & 0xFF) . chr(0) . chr(0));
            fwrite($fp, substr($framesData[$i], 13)); // Skip GIF header
        }
        
        // Write trailer
        fwrite($fp, chr(0x3B));
        fclose($fp);
    }
    
    imagedestroy($im);
}

function drawCharacter($im, $width, $height, $color, $frame, $totalFrames, $name) {
    $r = $color[0];
    $g = $color[1];
    $b = $color[2];
    
    $bodyColor = imagecolorallocatealpha($im, $r, $g, $b, 0);
    $darkColor = imagecolorallocatealpha($im, max(0, $r-50), max(0, $g-50), max(0, $b-50), 0);
    $lightColor = imagecolorallocatealpha($im, min(255, $r+50), min(255, $g+50), min(255, $b+50), 0);
    $eyeColor = imagecolorallocatealpha($im, 51, 38, 29, 0);
    $whiteColor = imagecolorallocatealpha($im, 255, 253, 247, 0);
    
    // Bounce animation
    $bounce = sin(($frame / $totalFrames) * 2 * M_PI) * 15;
    $centerX = $width / 2;
    $centerY = $height / 2 + $bounce;
    
    // Draw body (ellipse)
    imagefilledellipse($im, $centerX, $centerY + 20, 100, 80, $bodyColor);
    
    // Draw head
    imagefilledellipse($im, $centerX, $centerY - 40, 80, 70, $bodyColor);
    
    // Draw ears (simple triangles)
    $earY = $centerY - 70;
    // Left ear
    $leftEar = [$centerX - 30, $earY, $centerX - 45, $earY - 30, $centerX - 15, $earY - 10];
    imagefilledpolygon($im, $leftEar, 3, $bodyColor);
    // Right ear
    $rightEar = [$centerX + 30, $earY, $centerX + 15, $earY - 10, $centerX + 45, $earY - 30];
    imagefilledpolygon($im, $rightEar, 3, $bodyColor);
    
    // Inner ears
    $leftInnerEar = [$centerX - 28, $earY + 2, $centerX - 38, $earY - 20, $centerX - 18, $earY];
    imagefilledpolygon($im, $leftInnerEar, 3, $lightColor);
    $rightInnerEar = [$centerX + 28, $earY + 2, $centerX + 18, $earY, $centerX + 38, $earY - 20];
    imagefilledpolygon($im, $rightInnerEar, 3, $lightColor);
    
    // Eyes
    $eyeY = $centerY - 50;
    // Left eye
    imagefilledellipse($im, $centerX - 20, $eyeY, 16, 20, $whiteColor);
    imagefilledellipse($im, $centerX - 20, $eyeY, 8, 10, $eyeColor);
    // Right eye
    imagefilledellipse($im, $centerX + 20, $eyeY, 16, 20, $whiteColor);
    imagefilledellipse($im, $centerX + 20, $eyeY, 8, 10, $eyeColor);
    
    // Nose
    imagefilledellipse($im, $centerX, $centerY - 30, 12, 8, $darkColor);
    
    // Mouth
    $mouthY = $centerY - 20;
    for ($i = -8; $i <= 8; $i += 2) {
        $curveY = $mouthY + abs($i) * 0.5;
        imagefilledellipse($im, $centerX + $i, $curveY, 2, 2, $darkColor);
    }
    
    // Arms
    $armY = $centerY + 10;
    // Left arm
    imagefilledellipse($im, $centerX - 55, $armY + $bounce * 0.3, 20, 50, $bodyColor);
    // Right arm
    imagefilledellipse($im, $centerX + 55, $armY + $bounce * 0.3, 20, 50, $bodyColor);
    
    // Feet
    $footY = $centerY + 60;
    imagefilledellipse($im, $centerX - 25, $footY, 30, 15, $darkColor);
    imagefilledellipse($im, $centerX + 25, $footY, 30, 15, $darkColor);
}

function drawRainbowCharacter($im, $width, $height, $hue) {
    // Convert HSL to RGB
    $h = $hue / 360;
    $s = 1;
    $l = 0.5;
    
    $c = (1 - abs(2 * $l - 1)) * $s;
    $x = $c * (1 - abs(fmod($h * 6, 2) - 1));
    $m = $l - $c / 2;
    
    if ($h < 1/6) { $r = $c; $g = $x; $b = 0; }
    elseif ($h < 2/6) { $r = $x; $g = $c; $b = 0; }
    elseif ($h < 3/6) { $r = 0; $g = $c; $b = $x; }
    elseif ($h < 4/6) { $r = 0; $g = $x; $b = $c; }
    elseif ($h < 5/6) { $r = $x; $g = 0; $b = $c; }
    else { $r = $c; $g = 0; $b = $x; }
    
    $r = ($r + $m) * 255;
    $g = ($g + $m) * 255;
    $b = ($b + $m) * 255;
    
    $centerX = $width / 2;
    $centerY = $height / 2;
    
    $bodyColor = imagecolorallocatealpha($im, $r, $g, $b, 0);
    $whiteColor = imagecolorallocatealpha($im, 255, 253, 247, 0);
    $eyeColor = imagecolorallocatealpha($im, 51, 38, 29, 0);
    
    // Draw rainbow character
    imagefilledellipse($im, $centerX, $centerY + 20, 100, 80, $bodyColor);
    imagefilledellipse($im, $centerX, $centerY - 40, 80, 70, $bodyColor);
    
    // Ears
    $earY = $centerY - 70;
    $leftEar = [$centerX - 30, $earY, $centerX - 45, $earY - 30, $centerX - 15, $earY - 10];
    imagefilledpolygon($im, $leftEar, 3, $bodyColor);
    $rightEar = [$centerX + 30, $earY, $centerX + 15, $earY - 10, $centerX + 45, $earY - 30];
    imagefilledpolygon($im, $rightEar, 3, $bodyColor);
    
    // Eyes
    $eyeY = $centerY - 50;
    imagefilledellipse($im, $centerX - 20, $eyeY, 16, 20, $whiteColor);
    imagefilledellipse($im, $centerX - 20, $eyeY, 8, 10, $eyeColor);
    imagefilledellipse($im, $centerX + 20, $eyeY, 16, 20, $whiteColor);
    imagefilledellipse($im, $centerX + 20, $eyeY, 8, 10, $eyeColor);
    
    // Nose and mouth
    imagefilledellipse($im, $centerX, $centerY - 30, 12, 8, imagecolorallocatealpha($im, max(0,$r-50), max(0,$g-50), max(0,$b-50), 0));
    $mouthY = $centerY - 20;
    for ($i = -8; $i <= 8; $i += 2) {
        $curveY = $mouthY + abs($i) * 0.5;
        imagefilledellipse($im, $centerX + $i, $curveY, 2, 2, $eyeColor);
    }
}

echo "All GIFs created successfully!\n";