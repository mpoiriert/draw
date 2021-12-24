<?php

namespace Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes;

use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;

trait UtcPhpDateTimeMappingTypeTrait
{
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $timezone = date_default_timezone_get();
        date_default_timezone_set('UTC');

        try {
            if ($converted = parent::convertToPHPValue($value, $platform)) {
                $converted = $converted->setTimezone(new DateTimeZone($timezone));
            }

            return $converted;
        } finally {
            date_default_timezone_set($timezone);
        }
    }
}
