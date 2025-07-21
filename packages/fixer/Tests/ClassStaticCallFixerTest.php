<?php

namespace Draw\Fixer\Tests;

use Draw\Fixer\ClassNotation\ClassStaticCallFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ClassStaticCallFixerTest extends TestCase
{
    private ClassStaticCallFixer $object;

    protected function setUp(): void
    {
        $this->object = new ClassStaticCallFixer();
    }

    #[DataProvider('provideFixCases')]
    public function testFix(string $inCode, string $outCode): void
    {
        $tokens = Tokens::fromCode($inCode);
        $this->object->fix(new \SplFileInfo('test.php'), $tokens);
        static::assertSame($outCode, $tokens->generateCode());
    }

    public static function provideFixCases(): iterable
    {
        foreach (glob(__DIR__.'/fixtures/ClassStaticCallFixerTest/in/*.php') as $inFile) {
            $outFile = str_replace('/in/', '/out/', $inFile);
            yield basename($inFile) => [
                file_get_contents($inFile),
                file_get_contents($outFile),
            ];
        }
    }
}
