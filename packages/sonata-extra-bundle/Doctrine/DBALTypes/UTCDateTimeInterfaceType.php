<?php

namespace Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

abstract class UTCDateTimeInterfaceType extends DateTimeType
{
    /**
     * @var \DateTimeZone|null
     */
    private static $utc;

    /**
     * @param \DateTimeInterface $value
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        $typeClass = static::getTypeClass();
        if ($value instanceof $typeClass) {
            $value = $value->setTimezone(self::getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * @param \DateTimeInterface $value
     *
     * @throws ConversionException::conversionFailedFormat
     *
     * @return \DateTime
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        /** @var \DateTime $typeClass */
        $typeClass = static::getTypeClass();

        if (null === $value || $value instanceof $typeClass) {
            return $value;
        }

        $converted = $typeClass::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::getUtc()
        );

        if (!$converted) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $converted->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }

    abstract public static function getTypeClass(): string;

    private static function getUtc(): ?\DateTimeZone
    {
        return self::$utc ?: self::$utc = new \DateTimeZone('UTC');
    }
}
