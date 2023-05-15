<?php

namespace App\Controller\Api;

use Draw\Component\OpenApi\Schema as OpenApi;
use Symfony\Component\Routing\Annotation\Route;

#[OpenApi\Tag(name: 'Tags')]
class TestController
{
    #[Route(path: '/test', methods: ['GET'])]
    public function testAction(): void
    {
    }
}
