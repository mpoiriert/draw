<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\ConstraintRemoteFileExists;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ConstraintRemoteFileExistsValidatorTest extends TestCase
{
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

        $violations = $validator->validate($file, [new ConstraintRemoteFileExists()]);
        $this->assertEquals($violationsCount, $violations->count());
    }
}
