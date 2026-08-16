<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use RuntimeException;

class QrCodeService
{
    public function dataUri(string $pixPayload): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->svg($pixPayload));
    }

    public function svg(string $pixPayload): string
    {
        $pixPayload = trim($pixPayload);

        if ($pixPayload === '') {
            throw new RuntimeException('Payload PIX vazio para QR Code.');
        }

        if (!class_exists(Writer::class)) {
            throw new RuntimeException('Dependencia bacon/bacon-qr-code nao instalada. Execute composer install.');
        }

        $renderer = new ImageRenderer(
            new RendererStyle(350, 16),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString(
            $pixPayload,
            'UTF-8',
            ErrorCorrectionLevel::Q()
        );
    }
}
