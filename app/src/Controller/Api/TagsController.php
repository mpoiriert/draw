<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\Component\OpenApi\Serializer\Serialization;
use Draw\DoctrineExtra\ORM\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;

class TagsController
{
    /**
     * @return Tag The newly created tag
     */
    #[Route(path: '/tags', methods: ['POST'])]
    #[OpenApi\Operation(operationId: 'tagCreate')]
    #[Serialization(statusCode: 201)]
    public function createAction(
        #[RequestBody] Tag $target,
        EntityManagerInterface $entityManager
    ): Tag {
        $entityManager->persist($target);
        $entityManager->flush();

        return $target;
    }

    /**
     * @return Tag The update tag
     */
    #[Route(path: '/tags/{id}', methods: ['PUT'])]
    #[OpenApi\Operation(operationId: 'tagEdit')]
    public function editAction(
        #[RequestBody(propertiesMap: ['id' => 'id'])] Tag $target,
        EntityManagerInterface $entityManager
    ): Tag {
        $entityManager->flush();

        return $target;
    }

    /**
     * @return Tag The tag
     */
    #[Route(path: '/tags/{id}', name: 'tag_get', methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'tagGet')]
    public function getAction(Tag $target): Tag
    {
        return $target;
    }

    /**
     * @return void Empty response mean success
     */
    #[Route(path: '/tags/{id}', methods: ['DELETE'])]
    #[OpenApi\Operation(operationId: 'tagDelete')]
    public function deleteAction(Tag $target, EntityManagerInterface $entityManager): void
    {
        $entityManager->remove($target);
        $entityManager->flush();
    }

    /**
     * @return Tag[] All tags
     */
    #[Route(path: '/tags', methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'tagList')]
    public function listAction(
        EntityHandler $entityHandler,
        #[OpenApi\QueryParameter] ?bool $active = null
    ): array {
        if (null !== $active) {
            $entityHandler->findBy(Tag::class, ['active' => $active]);
        }

        return $entityHandler->findAll(Tag::class);
    }

    /**
     * @return void Empty return value mean success
     */
    #[Route(path: '/tags/activate-all', methods: ['POST'])]
    #[OpenApi\Operation(operationId: 'tagActivateAll')]
    #[Serialization(statusCode: 204)]
    public function activateAllAction(EntityHandler $entityHandler): void
    {
        foreach ($entityHandler->findBy(Tag::class, ['active' => false]) as $tag) {
            $tag->setActive(true);
        }

        $entityHandler->flush();
    }
}
