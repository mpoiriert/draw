<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeGenerator
{
    public function getTotpQrCode(string $qrCodeContent): ResultInterface
    {
        return Builder::create()
            ->writer(new SvgWriter())
            ->writerOptions([])
            ->data($qrCodeContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(200)
            ->margin(0)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();
    }
}
