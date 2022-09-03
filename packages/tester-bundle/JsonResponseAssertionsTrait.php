<?php

namespace Draw\Bundle\TesterBundle;

use Draw\Component\Tester\Data\AgainstJsonFileTester;
use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait;

trait JsonResponseAssertionsTrait
{
    use BrowserKitAssertionsTrait {
        getClient as protected;
        getResponse as protected;
        getRequest as protected;
    }

    public static function assertResponseIsJson(string $message = ''): void
    {
        TestCase::assertJson(
            static::getResponseContent(),
            $message,
        );
    }

    public static function getResponseContent(): ?string
    {
        return static::getResponse()->getContent();
    }

    public static function assertResponseJsonAgainstFile(string $file, array $propertyPathsCheck = []): void
    {
        (new DataTester(static::getResponse()->getContent()))
            ->transform('json_decode')
            ->test(
                new AgainstJsonFileTester(
                    $file,
                    $propertyPathsCheck
                )
            );
    }

    public static function getJsonResponseDataTester(): DataTester
    {
        return (new DataTester(static::getResponseContent()))
            ->transform('json_decode');
    }
}
