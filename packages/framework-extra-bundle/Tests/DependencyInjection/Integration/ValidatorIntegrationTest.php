<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ValidatorIntegration;
use Draw\Component\Validator\Constraints\PhpCallableValidator;
use Draw\Component\Validator\Constraints\RemoteFileExistsValidator;
use Draw\Component\Validator\Constraints\ValueIsNotUsedValidator;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @property ValidatorIntegration $integration
 */
#[CoversClass(ValidatorIntegration::class)]
class ValidatorIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): ValidatorIntegration
    {
        return new ValidatorIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'validator';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public static function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.validator.constraints.php_callable_validator',
                    [
                        PhpCallableValidator::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.validator.constraints.remote_file_exists_validator',
                    [
                        RemoteFileExistsValidator::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.validator.constraints.value_is_not_used_validator',
                    [
                        ValueIsNotUsedValidator::class,
                    ]
                ),
            ],
        ];
    }
}
