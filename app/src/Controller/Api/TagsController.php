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
     * @Route(methods={"POST"}, path="/tags")
     *
     * @return Tag The newly created tag
     */
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
     * @Route(methods={"PUT"}, path="/tags/{id}")
     *
     * @return Tag The update tag
     */
    #[OpenApi\Operation(operationId: 'tagEdit')]
    public function editAction(
        #[RequestBody(propertiesMap: ['id' => 'id'])] Tag $target,
        EntityManagerInterface $entityManager
    ): Tag {
        $entityManager->flush();

        return $target;
    }

    /**
     * @Route(name="tag_get", methods={"GET"}, path="/tags/{id}")
     *
     * @return Tag The tag
     */
    #[OpenApi\Operation(operationId: 'tagGet')]
    public function getAction(Tag $target): Tag
    {
        return $target;
    }

    /**
     * @Route(methods={"DELETE"}, path="/tags/{id}")
     *
     * @return void Empty response mean success
     */
    #[OpenApi\Operation(operationId: 'tagDelete')]
    public function deleteAction(Tag $target, EntityManagerInterface $entityManager): void
    {
        $entityManager->remove($target);
        $entityManager->flush();
    }

    /**
     * @Route(methods={"GET"}, path="/tags")
     *
     * @return Tag[] All tags
     */
    #[OpenApi\Operation(operationId: 'tagList')]
    public function listAction(EntityHandler $entityHandler): array
    {
        return $entityHandler->findAll(Tag::class);
    }

    /**
     * @Route(methods={"POST"}, path="/tags/activate-all")
     *
     * @return void Empty return value mean success
     */
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
