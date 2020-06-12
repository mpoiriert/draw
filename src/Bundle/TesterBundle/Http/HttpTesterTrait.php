<?php

namespace Draw\Bundle\TesterBundle\Http;

use Draw\Component\Tester\Http\Client;
use Draw\Component\Tester\Http\ClientInterface;

trait HttpTesterTrait
{
    use \Draw\Component\Tester\HttpTesterTrait;

    protected function createHttpTesterClient(): ClientInterface
    {
        return new Client(new RequestExecutioner($this));
    }
}
