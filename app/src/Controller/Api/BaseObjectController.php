<?php

namespace App\Controller\Api;

use App\Entity\BaseObject;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\OpenApi\Schema as OpenApi;
use Symfony\Component\Routing\Annotation\Route;

#[OpenApi\Tag(name: 'BaseObject')]
class BaseObjectController
{
    /**
     * Get all base objects.
     *
     * @return array<BaseObject>
     */
    #[Route('/base-objects', name: 'get_all_base_objects', methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'getAllBaseObjects')]
    public function getAll(EntityManagerInterface $entityManager): array
    {
        return $entityManager->getRepository(BaseObject::class)->findAll();
    }
}
