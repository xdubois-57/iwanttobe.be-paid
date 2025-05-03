<?php
// QrHelper.php - shared QR code generation helper
require_once __DIR__ . '/../vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrHelper {
    /**
     * Generate a QR code SVG for a given URL
     * @param string $url
     * @return string SVG markup
     */
    public static function renderSvg(string $url): string {
        $options = new QROptions([
            'eccLevel' => QRCode::ECC_L,     // Lowest error correction level
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'scale' => 4,                    // Slightly smaller scale
            'imageBase64' => false,
            'addQuietzone' => true,
            'quietzoneSize' => 1             // Smaller quiet zone
        ]);
        $qrcode = new QRCode($options);
        return $qrcode->render($url);
    }
}
