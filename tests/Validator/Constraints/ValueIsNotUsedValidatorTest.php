<?php

namespace App\Tests\Validator\Constraints;

use App\Entity\Tag;
use App\Entity\User;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Component\Validator\Constraints\ValueIsNotUsed;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
class ValueIsNotUsedValidatorTest extends KernelTestCase implements AutowiredInterface
{
    #[AutowireService]
    protected ValidatorInterface $validator;

    #[DataProvider('provideValidateCases')]
    public function testValidate(mixed $value, string $entityClass, string $field, bool $expectError): void
    {
        $violations = $this->validator
            ->validate($value, new ValueIsNotUsed(entityClass: $entityClass, field: $field))
        ;

        if (!$expectError) {
            static::assertCount(0, $violations);

            return;
        }

        static::assertCount(1, $violations);

        static::assertSame('VALUE_ALREADY_TAKEN', $violations->get(0)->getCode());
    }

    public static function provideValidateCases(): iterable
    {
        yield 'invalid' => [
            'admin@example.com',
            User::class,
            'email',
            true,
        ];

        yield 'not-used' => [
            uniqid().'@example.com',
            User::class,
            'email',
            false,
        ];

        yield 'other-entity' => [
            'admin@example.com',
            Tag::class,
            'name',
            false,
        ];
    }
}
