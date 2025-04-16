<?php
/**
 * Helper to generate a PNG image with centered text (for QR logo)
 */
class TextLogoHelper {
    /**
     * Generates a PNG file with the given text centered and rotated 45 degrees
     * @param string $text
     * @param string $fontPath
     * @param int $size (width/height in px)
     * @param string $outputFile (path to PNG file)
     * @return string path to PNG file
     */
    public static function makeTextLogo(string $text, string $fontPath, int $size, string $outputFile): string {
        $im = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefill($im, 0, 0, $white);
        $fontSize = $size * 0.18; // scale font to logo size
        $angle = 45;
        $bbox = imagettfbbox($fontSize, $angle, $fontPath, $text);
        $textWidth = abs($bbox[4] - $bbox[0]);
        $textHeight = abs($bbox[5] - $bbox[1]);
        $x = (int)(($size - $textWidth) / 2);
        $y = (int)(($size + $textHeight) / 2);
        imagettftext($im, $fontSize, $angle, $x, $y, $black, $fontPath, $text);
        imagepng($im, $outputFile);
        imagedestroy($im);
        return $outputFile;
    }
}
