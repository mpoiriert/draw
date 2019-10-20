<?php namespace Draw\Bundle\PostOfficeBundle\Email;

interface EmailWriterInterface
{
    public static function getForEmails(): array;
}