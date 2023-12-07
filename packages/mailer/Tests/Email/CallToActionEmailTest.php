<?php

namespace Draw\Component\Mailer\Tests\Email;

use Draw\Component\Mailer\Email\CallToActionEmail;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CallToActionEmail::class)]
class CallToActionEmailTest extends TestCase
{
    private CallToActionEmail $object;

    protected function setUp(): void
    {
        $this->object = new CallToActionEmail();
    }

    public function testCallToActionLinkMutator(): void
    {
        static::assertNull($this->object->getCallToActionLink());

        static::assertSame(
            $this->object,
            $this->object->callToActionLink($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->object->getCallToActionLink()
        );
    }

    public function testGetContext(): void
    {
        $this->object->context(['key' => 'value']);

        $this->object->callToActionLink($link = uniqid('link-'));

        static::assertSame(
            [
                'key' => 'value',
                'call_to_action_link' => $link,
                'translation_tokens' => [],
            ],
            $this->object->getContext()
        );
    }
}
