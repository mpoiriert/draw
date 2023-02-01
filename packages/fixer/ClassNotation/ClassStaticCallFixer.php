<?php

namespace Draw\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class ClassStaticCallFixer extends AbstractFixer
{
    public function getName(): string
    {
        return 'Draw/class_static_call';
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Converts self calls to static calls in class methods.',
            [
                new CodeSample(
                    '<?php
class Foo {
    public function bar()
    {
        self::baz();
    }

    public static function baz()
    {
        // some code
    }
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_CLASS);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokensToFind = [
            new Token([\T_STRING, 'self']),
            new Token([\T_STATIC, 'static']),
        ];

        for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
            if (!$tokens[$index]->isGivenKind(\T_CLASS)) {
                continue;
            }

            $classOpenIndex = $tokens->getNextTokenOfKind($index, [
                '{',
                ';',
            ]);

            if (!$tokens[$classOpenIndex]->equals('{')) {
                continue;
            }

            $classCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $classOpenIndex);

            $thisIndex = $tokens->getNextTokenOfKind(
                $classOpenIndex,
                $tokensToFind
            );

            while (null !== $thisIndex && $thisIndex < $classCloseIndex) {
                if (null === $nextIndex = $tokens->getNextMeaningfulToken($thisIndex)) {
                    continue;
                }

                if ($tokens[$nextIndex]->isGivenKind(\T_DOUBLE_COLON)) {
                    if ($this->isClassFinal($tokens, $nextIndex)) {
                        $tokens[$thisIndex] = new Token([\T_STRING, 'self']);
                    } elseif ($this->isMethodCall($tokens, $nextIndex)) {
                        $tokens[$thisIndex] = new Token([\T_STATIC, 'static']);
                    }
                }

                $thisIndex = $tokens->getNextTokenOfKind($thisIndex, $tokensToFind);
            }
        }
    }

    private function isClassFinal(Tokens $tokens, int $index): bool
    {
        $index = $tokens->getPrevTokenOfKind($index, [new Token([\T_CLASS, 'class'])]);

        if (null === $index) {
            return false;
        }

        $index = $tokens->getPrevMeaningfulToken($index);

        if (null === $index) {
            return false;
        }

        return $tokens[$index]->isGivenKind(\T_FINAL);
    }

    private function isMethodCall(Tokens $tokens, int $index): bool
    {
        if (null === $nextIndex = $tokens->getNextMeaningfulToken($index)) {
            return false;
        }

        $nextToken = $tokens[$nextIndex];

        if (!$nextToken->isGivenKind(\T_STRING)) {
            return false;
        }

        $nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
        $nextToken = $tokens[$nextIndex];

        return '(' === $nextToken->getContent();
    }
}
