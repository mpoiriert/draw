<?php

namespace Draw\Component\Mailer\Tests\Email;

use Draw\Component\Mailer\Email\CallToActionEmail;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Mailer\Email\CallToActionEmail
 */
class CallToActionEmailTest extends TestCase
{
    private CallToActionEmail $object;

    public function setUp(): void
    {
        $this->object = new CallToActionEmail();
    }

    public function testCallToActionLinkMutator(): void
    {
        $this->assertNull($this->object->getCallToActionLink());

        $this->assertSame(
            $this->object,
            $this->object->callToActionLink($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->object->getCallToActionLink()
        );
    }

    public function testGetContext(): void
    {
        $this->object->context(['key' => 'value']);

        $this->object->callToActionLink($link = uniqid('link-'));

        $this->assertSame(
            [
                'key' => 'value',
                'call_to_action_link' => $link,
            ],
            $this->object->getContext()
        );
    }
}
