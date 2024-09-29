<?php

namespace App\Tests;

use Draw\Bundle\TesterBundle\Mailer\TemplatedMailerAssertionsTrait;
use Draw\Bundle\TesterBundle\Messenger\MessengerTesterTrait;
use Draw\Bundle\TesterBundle\Profiling\MetricTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class TestCase extends KernelTestCase
{
    use MessengerTesterTrait;
    use MetricTesterTrait;
    use TemplatedMailerAssertionsTrait;
}
