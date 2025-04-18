<?php
/**
 * QRImageWithLogo - QR code output with logo overlay (text as logo)
 * Adapted from chillerlan/php-qrcode example
 */
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRCodeOutputException;

class QRImageWithLogo extends QRGdImagePNG {
    /**
     * @throws QRCodeOutputException
     */
    public function dump(?string $file = null, ?string $logo = null): string {
        $logo ??= '';
        $this->options->returnResource = true;
        parent::dump($file);

        if (!is_file($logo) || !is_readable($logo)) {
            throw new QRCodeOutputException('invalid logo');
        }
        $im = imagecreatefrompng($logo);
        if ($im === false) {
            throw new QRCodeOutputException('imagecreatefrompng() error');
        }
        $w = imagesx($im);
        $h = imagesy($im);
        $lw = (($this->options->logoSpaceWidth - 2) * $this->options->scale);
        $lh = (($this->options->logoSpaceHeight - 2) * $this->options->scale);
        $ql = ($this->matrix->getSize() * $this->options->scale);
        imagecopyresampled($this->image, $im, (($ql - $lw) / 2), (($ql - $lh) / 2), 0, 0, $lw, $lh, $w, $h);
        $imageData = $this->dumpImage();
        $this->saveToFile($imageData, $file);
        if ($this->options->outputBase64) {
            $imageData = $this->toBase64DataURI($imageData);
        }
        return $imageData;
    }
}
