<?php

namespace Draw\Bundle\TesterBundle\Tests\Messenger;

use Draw\Bundle\TesterBundle\Messenger\TransportTester;
use Draw\Bundle\TesterBundle\Tests\TestCase;
use Draw\Component\Core\FilterExpression\Expression\Expression;
use Draw\Component\Core\FilterExpression\Query;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Messenger\Envelope;

class TransportTesterTest extends TestCase
{
    /**
     * @var TransportTester
     */
    private $transportTester;

    public function setUp()
    {
        $this->transportTester = $this->getService('messenger.transport.memory.draw.tester');
    }

    public function testGetTransport(): void
    {
        $this->assertSame(
            $this->getService('messenger.transport.memory'),
            $this->transportTester->getTransport()
        );
    }

    public function testAssertMatch(): void
    {
        $transport = $this->transportTester->getTransport();
        $transport->send(new Envelope($object = new \stdClass()));
        $object->property = 'value';

        $this->transportTester->assertMessageMatch(
            (new Query())->where(Expression::andWhereEqual(['property' => 'value']))
        );
    }

    public function testAssertMatchFailed(): void
    {
        $transport = $this->transportTester->getTransport();
        $transport->send(new Envelope($object = new \stdClass()));
        $object->property = 'value';

        $this->expectException(ExpectationFailedException::class);

        $this->transportTester->assertMessageMatch(
            (new Query())->where(Expression::andWhereEqual(['property' => 'not-good']))
        );
    }
}