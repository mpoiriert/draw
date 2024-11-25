<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeGenerator
{
    public function getTotpQrCode(string $qrCodeContent): ResultInterface
    {
        return (new Builder(
            new SvgWriter(),
            data: $qrCodeContent,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 200,
            margin: 0,
        ))->build();
    }
}
