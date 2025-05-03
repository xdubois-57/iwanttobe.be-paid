<?php
/**
 * QrController
 * Global endpoint for generating QR code SVGs using QrHelper
 */
class GenericQrController {
    public function svg($params) {
        header('Content-Type: image/svg+xml');
        require_once __DIR__ . '/../lib/QrHelper.php';
        $data = $_GET['data'] ?? '';
        if (!$data) {
            http_response_code(400);
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><text x="10" y="100" font-size="14">Missing data</text></svg>';
            return;
        }
        echo QrHelper::renderSvg($data);
    }
}
