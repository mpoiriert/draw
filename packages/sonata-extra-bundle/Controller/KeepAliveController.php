<?php

namespace Draw\Bundle\SonataExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class KeepAliveController extends AbstractController
{
    /**
     * @Route(name="keep_alive", methods={"GET"}, path="/keep-alive")
     */
    public function keepAlive(): JsonResponse
    {
        return new JsonResponse();
    }
}
