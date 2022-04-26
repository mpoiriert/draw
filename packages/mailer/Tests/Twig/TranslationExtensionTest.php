<?php

namespace Draw\Component\Mailer\Tests\Twig;

use Draw\Component\Mailer\Twig\TranslationExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @covers \Draw\Component\Mailer\Twig\TranslationExtension
 */
class TranslationExtensionTest extends TestCase
{
    private TranslationExtension $object;

    private TranslatorInterface $translator;

    public function setUp(): void
    {
        $this->object = new TranslationExtension(
            $this->translator = $this->createMock(TranslatorInterface::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AbstractExtension::class,
            $this->object
        );
    }

    public function testGetFilters(): void
    {
        $filters = $this->object->getFilters();

        $this->assertCount(1, $filters);

        $filter = $filters[0];

        $this->assertInstanceOf(TwigFilter::class, $filter);

        $this->assertSame(
            'trans',
            $filter->getName(),
        );

        $this->assertSame(
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
        $count = rand(0, PHP_INT_MAX);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with(
                $message,
                array_merge($arguments, ['%count%' => $count]),
                $domain,
                $locale
            )
            ->willReturnArgument(0);

        $this->assertSame(
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
            ->expects($this->exactly(2))
            ->method('trans')
            ->withConsecutive(
                [$message1],
                [$message2]
            )
            ->willReturnOnConsecutiveCalls(
                $message1,
                $result = uniqid('result-')
            );

        $this->assertSame(
            $result,
            $this->object->trans(
                [$message1, $message2, uniqid('message-not-use-')],
            )
        );
    }
}
