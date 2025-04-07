<?php

class HomeController {
    private const SERVICE_TAG = 'BCD';
    private const VERSION = '002';
    private const ENCODING = '1';
    private const IDENTIFICATION = 'SCT';
    private const QR_API_URL = 'https://api.qrserver.com/v1/create-qr-code/';
    private const FONT_PATH = __DIR__ . '/../fonts/OpenSans-Regular.ttf';

    public function index() {
        require_once __DIR__ . '/../views/home.php';
    }

    public function about() {
        require_once __DIR__ . '/../views/about.php';
    }

    public function generateQR() {
        try {
            // Get POST data
            $name = $_POST['beneficiary_name'] ?? '';
            $iban = $_POST['beneficiary_iban'] ?? '';
            $amount = $_POST['amount'] ?? '';
            $communication = $_POST['communication'] ?? '';

            // Validate inputs
            if (empty($name) || empty($iban) || empty($amount)) {
                throw new Exception('Missing required fields');
            }

            // Clean and validate IBAN
            $iban = strtoupper(str_replace(' ', '', $iban));
            if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
                throw new Exception('Invalid IBAN format');
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

    private function lookupBIC($iban) {
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
            return ''; // Return empty string if BIC lookup fails
        }

        $data = json_decode($response, true);
        if (!$data || isset($data['valid']) && $data['valid'] === false) {
            return ''; // Return empty string if validation fails
        }

        return $data['bankData']['bic'] ?? ''; // Return BIC or empty string if not found
    }

    private function generateEPCData($name, $iban, $bic, $amount, $communication = '') {
        // Remove any special characters from name (keep only alphanumeric, spaces and /)
        $name = preg_replace('/[^a-zA-Z0-9\s\/]/', '', $name);
        
        // Format amount with exactly 2 decimal places
        $amount = number_format($amount, 2, '.', '');

        // Build the EPC QR code data
        $data = [
            self::SERVICE_TAG,
            self::VERSION,
            self::ENCODING,
            self::IDENTIFICATION,
            $bic,
            $name,
            $iban,
            'EUR' . $amount,
            'CHAR', // Purpose code (CHAR)
            '', // Remittance info (structured)
            $communication, // Remittance info (unstructured)
            '' // Purpose of credit transfer
        ];

        return implode("\n", array_map('trim', $data));
    }

    private function generateQRCode($text) {
        require_once __DIR__ . '/../controllers/LanguageController.php';
        $lang = LanguageController::getInstance();

        // Generate QR code using qrserver.com API
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
        
        // Fetch the QR code image
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

        // Create image from QR code
        $im = @imagecreatefromstring($qrImage);
        if ($im === false) {
            throw new Exception('Failed to process QR code image');
        }

        // Create a larger canvas to accommodate the text
        $width = imagesx($im);
        $height = imagesy($im) + 80; // Add more space for text
        $newIm = imagecreatetruecolor($width, $height);
        if ($newIm === false) {
            imagedestroy($im);
            throw new Exception('Failed to create image canvas');
        }

        // Fill background with white
        $white = imagecolorallocate($newIm, 255, 255, 255);
        imagefill($newIm, 0, 0, $white);

        // Copy QR code to new image
        imagecopy($newIm, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));

        // Add text
        $black = imagecolorallocate($newIm, 0, 0, 0);
        $gray = imagecolorallocate($newIm, 100, 100, 100);
        
        // Use OpenSans font
        $fontSize = 12;
        $fontPath = self::FONT_PATH;
        
        // Get payment details from EPC data
        $epcLines = explode("\n", $text);
        $name = rtrim($epcLines[5]); // Remove all trailing whitespace and newlines
        $iban = rtrim($epcLines[6]); // IBAN is on line 3 (0-based index)
        $amountStr = rtrim($epcLines[7]); // Remove all trailing whitespace and newlines
        $amount = floatval(str_replace('EUR', '', $amountStr)); // Remove EUR and convert to float
        
        // Format texts
        $generatedBy = $lang->translate('generated_by');
        $summaryText = sprintf(
            "%s\nIBAN: %s\n%.2f EUR",
            sprintf($lang->translate('payment_to'), $name),
            $iban,
            $amount
        );
        
        // Calculate and draw "Generated by" text
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $generatedBy);
        if ($bbox === false) {
            throw new Exception('Failed to calculate text dimensions');
        }
        
        $textWidth = abs($bbox[4] - $bbox[0]);
        $x = intval(($width - $textWidth) / 2);
        $y = intval($height - 55);

        // Add "Generated by" text
        $result = imagettftext($newIm, $fontSize, 0, $x, $y, $black, $fontPath, $generatedBy);
        if ($result === false) {
            throw new Exception('Failed to add text to image');
        }

        // Draw each line of the summary text
        $lines = explode("\n", $summaryText);
        $smallerFontSize = 10;
        $lineHeight = 15;
        
        foreach ($lines as $index => $line) {
            $bbox = imagettfbbox($smallerFontSize, 0, $fontPath, $line);
            if ($bbox === false) {
                continue;
            }
            
            $textWidth = abs($bbox[4] - $bbox[0]);
            $x = intval(($width - $textWidth) / 2);
            $y = intval($height - 35 + ($index * $lineHeight));
            
            imagettftext($newIm, $smallerFontSize, 0, $x, $y, $gray, $fontPath, $line);
        }

        // Capture the image data
        ob_start();
        imagepng($newIm);
        $imageData = ob_get_clean();

        // Clean up
        imagedestroy($im);
        imagedestroy($newIm);

        // Return base64 encoded image
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}
