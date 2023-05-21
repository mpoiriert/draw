<?php

namespace Draw\DoctrineExtra\DBAL\Types;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * Type that maps a database BIGINT to a PHP int instead of a string.
 * We recommend using this type instead of the default Doctrine BigIntType if you are on a 64-bit system.
 * Make sure to not use unsigned bigint columns as the max value will be too high.
 */
class BigIntType extends Type
{
    public function getName(): string
    {
        return Types::BIGINT;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBigIntTypeDeclarationSQL($column);
    }

    public function getBindingType(): int
    {
        return ParameterType::INTEGER;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return null === $value ? null : (int) $value;
    }
}
