<?php

class HomeController {
    private const SERVICE_TAG = 'BCD';
    private const VERSION = '002';
    private const ENCODING = '1';
    private const IDENTIFICATION = 'SCT';
    private const QR_API_URL = 'https://api.qrserver.com/v1/create-qr-code/';

    public function index() {
        require_once __DIR__ . '/../views/home.php';
    }

    public function about() {
        require_once __DIR__ . '/../views/about.php';
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
            $error = error_get_last();
            throw new Exception('Failed to connect to BIC lookup service: ' . ($error['message'] ?? 'Unknown error'));
        }

        // Check for HTTP errors
        if (isset($http_response_header[0])) {
            $status_line = $http_response_header[0];
            if (strpos($status_line, '200') === false) {
                throw new Exception('BIC lookup service error: ' . $status_line);
            }
        }

        $data = json_decode($response, true);
        if (!$data) {
            throw new Exception('Invalid JSON response from BIC lookup: ' . substr($response, 0, 100));
        }

        if (isset($data['valid']) && $data['valid'] === false) {
            throw new Exception('Invalid IBAN: ' . ($data['messages'][0] ?? 'No specific error message'));
        }

        if (!isset($data['bankData']['bic'])) {
            throw new Exception('No BIC found in response. Full response: ' . json_encode($data));
        }

        return $data['bankData']['bic'];
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

        $imageData = @file_get_contents($url, false, $context);
        
        if ($imageData === false) {
            throw new Exception('Failed to generate QR code');
        }

        // Check if we got an image
        if (!isset($http_response_header[0]) || strpos($http_response_header[0], '200') === false) {
            throw new Exception('QR code service error');
        }

        // Convert to base64
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    public function generateQR() {
        try {
            // Enable error reporting for this request
            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            // Validate input
            $input = file_get_contents('php://input');
            if ($input === false) {
                throw new Exception('Failed to read request data');
            }

            $data = json_decode($input, true);
            if (!$data) {
                throw new Exception('Invalid JSON data received. Raw input: ' . substr($input, 0, 100));
            }
            
            if (!isset($data['beneficiary_name']) || 
                !isset($data['beneficiary_iban']) || 
                !isset($data['amount'])) {
                throw new Exception('Missing required fields. Received fields: ' . implode(', ', array_keys($data)));
            }

            // Validate IBAN format
            $iban = str_replace(' ', '', strtoupper($data['beneficiary_iban']));
            if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
                throw new Exception('Invalid IBAN format: ' . $iban);
            }

            // Validate amount
            $amount = floatval($data['amount']);
            if ($amount <= 0 || $amount > 999999999.99) {
                throw new Exception('Invalid amount: ' . $amount);
            }

            // Get BIC from IBAN
            $bic = $this->lookupBIC($iban);

            // Generate EPC QR code data
            $epcData = $this->generateEPCData(
                $data['beneficiary_name'],
                $iban,
                $bic,
                $amount,
                isset($data['communication']) ? trim($data['communication']) : ''
            );

            // Generate QR code as base64 image
            $qrImage = $this->generateQRCode($epcData);

            $response = [
                'success' => true,
                'qr_url' => $qrImage,
                'debug' => [
                    'epc_data' => $epcData,
                    'bic' => $bic
                ]
            ];

            header('Content-Type: application/json');
            echo json_encode($response);

        } catch (Exception $e) {
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $data ?? null,
                'php_version' => PHP_VERSION,
                'date' => date('Y-m-d H:i:s')
            ];
            
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
                'details' => $errorDetails
            ]);
        }
    }
}
