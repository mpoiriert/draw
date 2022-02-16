<?php

namespace Draw\Bundle\PostOfficeBundle\EmailWriter;

interface EmailWriterInterface
{
    public static function getForEmails(): array;
}
