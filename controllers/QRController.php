<?php
class QRController {
    private const SERVICE_TAG = 'BCD';
    private const VERSION = '002';
    private const IDENTIFICATION = 'SCT';
    private const QR_API_URL = 'https://api.qrserver.com/v1/create-qr-code/';
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
            $iban,
            'EUR' . $amount,
            'CHAR', // Purpose
            '', // Structured remittance
            $communication,
            '' // Purpose of transfer
        ];

        return implode("\n", array_map('trim', $data));
    }

    public function generateQRCode($text) {
        require_once __DIR__ . '/../controllers/LanguageController.php';
        $lang = LanguageController::getInstance();

        $params = [
            'data' => $text,
            'size' => '300x300',
            'charset-source' => 'UTF-8',
            'charset-target' => 'UTF-8',
            'ecc' => 'M',
            'margin' => '2',
            'format' => 'png'
        ];

        $url = self::QR_API_URL . '?' . http_build_query($params);
        
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 10,
                'user_agent' => 'QRTransfer/1.0'
            ]
        ]);

        $qrImage = @file_get_contents($url, false, $context);
        if ($qrImage === false) {
            throw new Exception('Failed to generate QR code');
        }

        $im = @imagecreatefromstring($qrImage);
        if ($im === false) {
            throw new Exception('Failed to process QR code image');
        }

        $width = imagesx($im);
        $height = imagesy($im) + 80;
        $newIm = imagecreatetruecolor($width, $height);
        if ($newIm === false) {
            imagedestroy($im);
            throw new Exception('Failed to create image canvas');
        }

        $white = imagecolorallocate($newIm, 255, 255, 255);
        imagefill($newIm, 0, 0, $white);
        imagecopy($newIm, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));

        $black = imagecolorallocate($newIm, 0, 0, 0);
        $gray = imagecolorallocate($newIm, 100, 100, 100);
        
        $fontSize = 12;
        $fontPath = self::FONT_PATH;
        
        $epcLines = explode("\n", $text);
        $name = rtrim($epcLines[5]);
        $iban = rtrim($epcLines[6]);
        $amountStr = rtrim($epcLines[7]);
        $amount = floatval(str_replace('EUR', '', $amountStr));
        
        $generatedBy = $lang->translate('generated_by');
        $summaryText = sprintf(
            "%s\nIBAN: %s\n%.2f EUR",
            sprintf($lang->translate('payment_to'), $name),
            $iban,
            $amount
        );
        
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $generatedBy);
        if ($bbox === false) {
            throw new Exception('Failed to calculate text dimensions');
        }
        
        $textWidth = abs($bbox[4] - $bbox[0]);
        $x = intval(($width - $textWidth) / 2);
        $y = intval($height - 55);

        $result = imagettftext($newIm, $fontSize, 0, $x, $y, $black, $fontPath, $generatedBy);
        if ($result === false) {
            throw new Exception('Failed to add text to image');
        }

        $lines = explode("\n", $summaryText);
        $smallerFontSize = 10;
        $lineHeight = 15;
        
        foreach ($lines as $index => $line) {
            $bbox = imagettfbbox($smallerFontSize, 0, $fontPath, $line);
            if ($bbox === false) continue;
            
            $textWidth = abs($bbox[4] - $bbox[0]);
            $x = intval(($width - $textWidth) / 2);
            $y = intval($height - 35 + ($index * $lineHeight));
            
            imagettftext($newIm, $smallerFontSize, 0, $x, $y, $gray, $fontPath, $line);
        }

        ob_start();
        imagepng($newIm);
        $imageData = ob_get_clean();

        imagedestroy($im);
        imagedestroy($newIm);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}
