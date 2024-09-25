<?php

namespace Draw\Fixer\Tests;

use Draw\Fixer\ClassNotation\ClassPrivateStaticCallFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClassPrivateStaticCallFixerTest extends TestCase
{
    private ClassPrivateStaticCallFixer $object;

    protected function setUp(): void
    {
        $this->object = new ClassPrivateStaticCallFixer();
    }

    public static function provideTestFix(): iterable
    {
        foreach (glob(__DIR__.'/fixtures/ClassPrivateStaticCallFixerTest/in/*.php') as $inFile) {
            $outFile = str_replace('/in/', '/out/', $inFile);
            yield basename($inFile) => [
                file_get_contents($inFile),
                file_get_contents($outFile),
            ];
        }
    }

    #[DataProvider('provideTestFix')]
    public function testFix(string $inCode, string $outCode): void
    {
        $tokens = Tokens::fromCode($inCode);
        $this->object->fix(new \SplFileInfo('test.php'), $tokens);
        static::assertSame($outCode, $tokens->generateCode());
    }
}
