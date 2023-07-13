<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class MercureController extends AbstractController
{
    #[Route(path: '/mercure', methods: ['GET'])]
    public function indexAction(): Response
    {
        return $this->render('chat.html.twig');
    }

    #[Route(path: '/chat/send', name: 'chat-send', methods: ['POST'])]
    public function publish(HubInterface $hub, Request $request): Response
    {
        $hub->publish(
            new Update(
                'https://example.com/chat-rooms/1',
                json_encode(
                    [
                        'type' => 'chat',
                        'payload' => [
                            'message' => $request->request->get('message')
                        ]
                ])
            )
        );

        $hub->publish(
            new Update(
                'https://example.com/chat-rooms/1',
                json_encode(
                    [
                        'type' => 'attack',
                        'payload' => [
                            'attacker' => 1,
                            'target' => 2,
                            'receivedDamage' => 200,
                            'energyLeft' => 100,
                        ]
                    ])
            )
        );

        return new Response('published!');
    }
}