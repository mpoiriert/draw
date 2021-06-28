<?php

namespace Draw\Bundle\TesterBundle\Tests\Mock\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    /**
     * @Route(path="/test")
     *
     */
    public function testAction(): Response
    {
        return new Response();
    }
}
