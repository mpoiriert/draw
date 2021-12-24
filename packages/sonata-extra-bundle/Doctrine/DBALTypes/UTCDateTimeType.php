<?php

namespace Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes;

class UTCDateTimeType extends UTCDateTimeInterfaceType
{
    public static function getTypeClass(): string
    {
        return \DateTime::class;
    }
}
