<?php
/**
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Handles all QR code generation functionality
 * 
 * Responsibilities:
 * - Validating payment details
 * - Looking up BIC codes
 * - Generating EPC QR code data
 * - Creating visual QR codes with payment details
 * - Error handling for QR generation
 * 
 * Usage:
 * $qrController = new QRController();
 * $qrImage = $qrController->generate($name, $iban, $amount, $communication);
 */
class QRController {
    private const SERVICE_TAG = 'BCD';
    private const VERSION = '002';
    private const IDENTIFICATION = 'SCT';
    private const FONT_PATH = __DIR__ . '/../fonts/OpenSans-Regular.ttf';

    public function generate() {
        try {
            $name = $_POST['beneficiary_name'] ?? '';
            $iban = $_POST['beneficiary_iban'] ?? '';
            $amount = $_POST['amount'] ?? '';
            $communication = $_POST['communication'] ?? '';

            // Validate inputs
            if (empty($name) || empty($iban) || empty($amount)) {
                throw new Exception('Missing required fields');
            }

            // Validate amount
            $amount = floatval($amount);
            if ($amount <= 0 || $amount > 999999999.99) {
                throw new Exception('Invalid amount');
            }

            // Get BIC code
            $bic = $this->lookupBIC($iban);

            // Generate EPC QR code data
            $epcData = $this->generateEPCData($name, $iban, $bic, $amount, $communication);

            // Generate QR code with caption
            $qrImage = $this->generateQRCode($epcData);

            // Return success response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'image' => $qrImage
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function lookupBIC($iban) {
        $url = 'https://openiban.com/validate/' . urlencode($iban) . '?getBIC=true&validateBankCode=true';
        
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 10,
                'user_agent' => 'QRTransfer/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return '';
        }

        $data = json_decode($response, true);
        return $data['bankData']['bic'] ?? '';
    }

    public function generateEPCData($name, $iban, $bic, $amount, $communication = '') {
        // Remove any special characters from name (keep only alphanumeric, spaces and /)
        $name = preg_replace('/[^a-zA-Z0-9\s\/]/', '', $name);
        
        // Format amount with exactly 2 decimal places
        $amount = number_format($amount, 2, '.', '');

        // Build the EPC QR code data
        $data = [
            self::SERVICE_TAG,
            self::VERSION,
            '1', // UTF-8
            self::IDENTIFICATION,
            $bic,
            $name,
            str_replace(' ', '', $iban),
            'EUR' . $amount,
            'CHAR', // Purpose
            '', // Structured remittance
            $communication,
            '' // Purpose of transfer
        ];

        return implode("\n", array_map('trim', $data));
    }

    /**
     * Generates a QR code image and outputs it as PNG.
     * @param string $data The data to encode in the QR code
     * @param int $size The size of the QR code (pixels)
     * @param bool $outputDirectly If true, outputs image to browser. If false, returns PNG binary string.
     * @return string|null PNG image data if $outputDirectly is false, otherwise null
     */
    public static function generateQrPng(string $data, int $size = 300, bool $outputDirectly = false): ?string {
        $options = new \chillerlan\QRCode\QROptions([
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false,
            'scale' => max(1, intval($size / 33)), // 33 is the default QR code matrix size
            'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
        ]);

        $qrcode = new \chillerlan\QRCode\QRCode($options);
        $pngData = $qrcode->render($data);

        if ($outputDirectly) {
            header('Content-Type: image/png');
            echo $pngData;
            return null;
        }
        return $pngData;
    }

    public function generateQRCode($text) {
        require_once __DIR__ . '/../controllers/LanguageController.php';
        require_once __DIR__ . '/../lib/QRImageWithLogo.php';
        require_once __DIR__ . '/../lib/TextLogoHelper.php';
        $lang = LanguageController::getInstance();

        // QR options
        $options = new \chillerlan\QRCode\QROptions([
            'outputBase64'        => false,
            'scale'               => 6,
            'imageTransparent'    => false,
            'drawCircularModules' => true,
            'circleRadius'        => 0.45,
            'keepAsSquare'        => [
                \chillerlan\QRCode\Data\QRMatrix::M_FINDER,
                \chillerlan\QRCode\Data\QRMatrix::M_FINDER_DOT,
            ],
            'eccLevel'            => \chillerlan\QRCode\Common\EccLevel::H,
            'addLogoSpace'        => true,
            'logoSpaceWidth'      => 13,
            'logoSpaceHeight'     => 13,
        ]);
        $qrcode = new \chillerlan\QRCode\QRCode($options);
        $qrcode->addByteSegment($text);
        $matrix = $qrcode->getQRMatrix();

        // Generate logo image with text
        $fontPath = __DIR__ . '/../fonts/OpenSans-Regular.ttf';
        $logoFile = sys_get_temp_dir() . '/qr_logo_text.png';
        \TextLogoHelper::makeTextLogo('iwantto.be', $fontPath, 156, $logoFile); // 156px = 13*12 (scale)

        // Output QR with logo
        $qrOutput = new \QRImageWithLogo($options, $matrix);
        $qrImageData = $qrOutput->dump(null, $logoFile);

        // Remove temp logo file
        @unlink($logoFile);

        // --- Compose final image with payment info below QR ---
        $qrIm = imagecreatefromstring($qrImageData);
        if ($qrIm === false) {
            throw new \Exception('Failed to process QR code image');
        }
        $width = imagesx($qrIm);
        $height = imagesy($qrIm);
        $extraHeight = 100;
        $finalIm = imagecreatetruecolor($width, $height + $extraHeight);
        $white = imagecolorallocate($finalIm, 255, 255, 255);
        imagefill($finalIm, 0, 0, $white);
        imagecopy($finalIm, $qrIm, 0, 0, 0, 0, $width, $height);
        $black = imagecolorallocate($finalIm, 0, 0, 0);
        $gray = imagecolorallocate($finalIm, 100, 100, 100);
        $fontSize = 12;
        $fontPath = self::FONT_PATH;
        // Parse EPC for info
        $epcLines = explode("\n", $text);
        $name = rtrim($epcLines[5]);
        $iban = rtrim($epcLines[6]);
        $amountStr = rtrim($epcLines[7]);
        $amount = floatval(str_replace('EUR', '', $amountStr));
        $communication = rtrim($epcLines[10]);
        $generatedBy = $lang->translate('generated_by');
        $summaryText = sprintf(
            "%s\n%s\n%.2f EUR\n%s",
            sprintf($lang->translate('payment_to'), $name),
            $iban,
            $amount,
            $communication
        );
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $generatedBy);
        $textWidth = abs($bbox[4] - $bbox[0]);
        $x = intval(($width - $textWidth) / 2);
        $y = intval($height + 25);
        imagettftext($finalIm, $fontSize, 0, $x, $y, $black, $fontPath, $generatedBy);
        $lines = explode("\n", $summaryText);
        $smallerFontSize = 10;
        $lineHeight = 15;
        foreach ($lines as $index => $line) {
            $bbox = imagettfbbox($smallerFontSize, 0, $fontPath, $line);
            if ($bbox === false) continue;
            $textWidth = abs($bbox[4] - $bbox[0]);
            $x = intval(($width - $textWidth) / 2);
            $y = intval($height + 45 + ($index * $lineHeight));
            imagettftext($finalIm, $smallerFontSize, 0, $x, $y, $gray, $fontPath, $line);
        }
        ob_start();
        imagepng($finalIm);
        $imageData = ob_get_clean();
        imagedestroy($qrIm);
        imagedestroy($finalIm);
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}
