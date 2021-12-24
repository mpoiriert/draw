<?php

namespace Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes;

class UTCDateTimeImmutableType extends UTCDateTimeInterfaceType
{
    public static function getTypeClass(): string
    {
        return \DateTimeImmutable::class;
    }
}
