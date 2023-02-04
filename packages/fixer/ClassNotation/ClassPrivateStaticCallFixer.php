<?php

namespace Draw\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class ClassPrivateStaticCallFixer extends AbstractFixer
{
    public function getName(): string
    {
        return 'Draw/class_private_static_call';
    }

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Converts static calls to self calls in class if methods are private.',
            [
                new CodeSample(
                    '<?php
class Foo {
    public function bar()
    {
        static::baz();
    }

    private static function baz()
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

            $thisIndex = $tokens->getNextTokenOfKind($classOpenIndex, [new Token([\T_STATIC, 'static'])]);

            while (null !== $thisIndex && $thisIndex < $classCloseIndex) {
                if (null !== $nextIndex = $tokens->getNextMeaningfulToken($thisIndex)) {
                    if ($tokens[$nextIndex]->isGivenKind(\T_DOUBLE_COLON)) {
                        if ($this->referenceIsPrivate($tokens, $nextIndex)) {
                            $tokens[$thisIndex] = new Token([\T_STRING, 'self']);
                        }
                    }
                }

                $thisIndex = $tokens->getNextTokenOfKind($thisIndex, [new Token([\T_STATIC, 'static'])]);
            }
        }
    }

    private function referenceIsPrivate(Tokens $tokens, int $index): bool
    {
        if (null === $nextIndex = $tokens->getNextMeaningfulToken($index)) {
            return false;
        }

        $nextToken = $tokens[$nextIndex];

        if (
            !$nextToken->isGivenKind(\T_STRING)
            && !$nextToken->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)
            && !$nextToken->isGivenKind(\T_VARIABLE)
        ) {
            return false;
        }

        $referencedName = $nextToken->getContent();

        $nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
        $nextToken = $tokens[$nextIndex];

        if ('(' === $nextToken->getContent()) {
            $type = 'method';
        } elseif (str_starts_with($referencedName, '$')) {
            $type = 'property';
        } else {
            $type = 'constant';
        }

        $result = false;
        switch ($type) {
            case 'method':
                $result = $this->hasPrivateAccessor($tokens, $referencedName, \T_FUNCTION);
                break;
            case 'property':
                $result = $this->hasPrivateAccessor($tokens, $referencedName, \T_VARIABLE);
                break;
            case 'constant':
                $result = $this->hasPrivateAccessor($tokens, $referencedName, \T_CONST);
                break;
        }

        return $result;
    }

    private function hasPrivateAccessor(Tokens $tokens, string $methodName, int $givenKind): bool
    {
        // Iterate through the tokens, searching for the method declaration
        for ($index = 0; $index < $tokens->count(); ++$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind($givenKind)) {
                continue;
            }

            if (\T_VARIABLE === $givenKind) {
                if ($token->getContent() !== $methodName) {
                    continue;
                }
            } else {
                $otherIndex = $tokens->getNextMeaningfulToken($index);
                if ($tokens[$otherIndex]->getContent() !== $methodName) {
                    continue;
                }
            }

            $visibilityIndex = $tokens->getPrevTokenOfKind($index, [
                new Token([\T_PRIVATE, 'private']),
                new Token([\T_PROTECTED, 'protected']),
                new Token([\T_PUBLIC, 'public']),
            ]);

            if (null === $visibilityIndex) {
                return false;
            }

            return $tokens[$visibilityIndex]->isGivenKind(\T_PRIVATE);
        }

        // Return false if we couldn't find the method
        return false;
    }
}
