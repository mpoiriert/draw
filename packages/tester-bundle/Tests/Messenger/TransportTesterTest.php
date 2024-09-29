<?php

namespace Draw\Bundle\TesterBundle\Tests\Messenger;

use Draw\Bundle\TesterBundle\Messenger\TransportTester;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireTransportTester;
use Draw\Bundle\TesterBundle\Tests\TestCase;
use Draw\Component\Core\FilterExpression\Expression\Expression;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Messenger\Envelope;

/**
 * @internal
 */
class TransportTesterTest extends TestCase implements AutowiredInterface
{
    #[AutowireTransportTester('memory')]
    private TransportTester $transportTester;

    public function testGetTransport(): void
    {
        static::assertSame(
            static::getContainer()->get('messenger.transport.memory'),
            $this->transportTester->getTransport()
        );
    }

    public function testAssertMatch(): void
    {
        $transport = $this->transportTester->getTransport();
        $transport->send(new Envelope($object = new \stdClass()));
        $object->property = 'value';

        $this->transportTester->assertMessageMatch(
            \stdClass::class,
            Expression::andWhereEqual(['property' => 'value'])
        );
    }

    public function testAssertMatchFailed(): void
    {
        $transport = $this->transportTester->getTransport();
        $transport->send(new Envelope($object = new \stdClass()));
        $object->property = 'value';

        $this->expectException(ExpectationFailedException::class);

        $this->transportTester->assertMessageMatch(\Exception::class);
    }
}
