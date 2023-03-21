<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PingController
{
    #[Route(path: '/ping', methods: ['GET'])]
    public function ping(EntityManagerInterface $entityManager): Response
    {
        $entityManager->getConnection()->executeQuery('SELECT "test"');

        return new Response('pong');
    }
}
