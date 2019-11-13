<?php namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PingController
{
    /**
     * @Route(methods={"GET"}, path="/ping")
     * @return Response
     */
    public function ping(EntityManagerInterface $entityManager)
    {
        $entityManager->getConnection()->executeQuery('SELECT "test"');
        return new Response('pong');
    }
}