<?php

namespace Draw\Component\Mailer\Tests\Twig;

use Draw\Component\Mailer\Twig\TranslationExtension;
use Draw\Component\Tester\DoubleTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFilter;

/**
 * @internal
 */
#[CoversClass(TranslationExtension::class)]
class TranslationExtensionTest extends TestCase
{
    use DoubleTrait;

    public function testGetFilters(): void
    {
        $object = new TranslationExtension(
            static::createStub(TranslatorInterface::class)
        );

        $filters = $object->getFilters();

        static::assertCount(1, $filters);

        $filter = $filters[0];

        static::assertInstanceOf(TwigFilter::class, $filter);

        static::assertSame(
            'trans',
            $filter->getName(),
        );

        static::assertSame(
            [$object, 'trans'],
            $filter->getCallable()
        );
    }

    public function testTrans(): void
    {
        $object = new TranslationExtension(
            $translator = $this->createMock(TranslatorInterface::class)
        );

        $message = uniqid('message-');
        $arguments = ['key' => uniqid('value-')];
        $domain = uniqid('domain-');
        $locale = uniqid('locale-');
        $count = random_int(0, \PHP_INT_MAX);

        $translator
            ->expects(static::once())
            ->method('trans')
            ->with(
                $message,
                [...$arguments, ...['%count%' => $count]],
                $domain,
                $locale
            )
            ->willReturnArgument(0)
        ;

        static::assertSame(
            $message,
            $object->trans(
                $message,
                $arguments,
                $domain,
                $locale,
                $count
            )
        );
    }

    public function testTransMultipleMessage(): void
    {
        $object = new TranslationExtension(
            $translator = $this->createMock(TranslatorInterface::class)
        );

        $message1 = uniqid('message-');
        $message2 = uniqid('message-');

        $translator
            ->expects(static::exactly(2))
            ->method('trans')
            ->with(
                ...static::withConsecutive(
                    [$message1],
                    [$message2]
                )
            )
            ->willReturnOnConsecutiveCalls(
                $message1,
                $result = uniqid('result-')
            )
        ;

        static::assertSame(
            $result,
            $object->trans(
                [$message1, $message2, uniqid('message-not-use-')],
            )
        );
    }
}
