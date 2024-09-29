<?php

namespace Draw\Component\Mailer\Tests\Twig;

use Draw\Component\Mailer\Twig\TranslationExtension;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @internal
 */
#[CoversClass(TranslationExtension::class)]
class TranslationExtensionTest extends TestCase
{
    use MockTrait;

    private TranslationExtension $object;

    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->object = new TranslationExtension(
            $this->translator = $this->createMock(TranslatorInterface::class)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            AbstractExtension::class,
            $this->object
        );
    }

    public function testGetFilters(): void
    {
        $filters = $this->object->getFilters();

        static::assertCount(1, $filters);

        $filter = $filters[0];

        static::assertInstanceOf(TwigFilter::class, $filter);

        static::assertSame(
            'trans',
            $filter->getName(),
        );

        static::assertSame(
            [$this->object, 'trans'],
            $filter->getCallable()
        );
    }

    public function testTrans(): void
    {
        $message = uniqid('message-');
        $arguments = ['key' => uniqid('value-')];
        $domain = uniqid('domain-');
        $locale = uniqid('locale-');
        $count = random_int(0, \PHP_INT_MAX);

        $this->translator
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
            $this->object->trans(
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
        $message1 = uniqid('message-');
        $message2 = uniqid('message-');

        $this->translator
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
            $this->object->trans(
                [$message1, $message2, uniqid('message-not-use-')],
            )
        );
    }
}
