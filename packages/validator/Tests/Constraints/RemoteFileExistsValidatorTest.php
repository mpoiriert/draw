<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\RemoteFileExists;
use Draw\Component\Validator\Constraints\RemoteFileExistsValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @covers \Draw\Component\Validator\Constraints\RemoteFileExistsValidator
 */
class RemoteFileExistsValidatorTest extends TestCase
{
    private RemoteFileExistsValidator $object;

    public function setUp(): void
    {
        $this->object = new RemoteFileExistsValidator();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            ConstraintValidatorInterface::class,
            $this->object
        );
    }

    public function testValidateInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Expected argument of type "%s", "%s" given',
                RemoteFileExists::class,
                NotNull::class
            )
        );

        $this->object->validate(null, new NotNull());
    }

    public function provideFiles(): array
    {
        return [
            'url' => ['https://github.com', 0],
            'image' => ['https://github.githubassets.com/images/modules/open_graph/github-logo.png', 0],
            'not-exist-url' => ['https://github-01.com', 1],
            'not-exist-image' => ['https://github.githubassets.com/images/modules/open_graph/github-logo-not-exist.png', 1],
        ];
    }

    /**
     * @dataProvider provideFiles
     */
    public function testValidate(string $file, int $violationsCount): void
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($file, [new RemoteFileExists()]);
        $this->assertEquals($violationsCount, $violations->count());
    }
}
