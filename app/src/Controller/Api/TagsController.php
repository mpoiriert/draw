<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\OpenApi\Configuration\Deserialization;
use Draw\Component\OpenApi\Configuration\Serialization;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\DoctrineExtra\ORM\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;

class TagsController
{
    /**
     * @Route(methods={"POST"}, path="/tags")
     *
     * @OpenApi\Operation(operationId="tagCreate")
     *
     * @Deserialization
     *
     * @Serialization(statusCode=201)
     *
     * @return Tag The newly created tag
     */
    public function createAction(Tag $target, EntityManagerInterface $entityManager): Tag
    {
        $entityManager->persist($target);
        $entityManager->flush();

        return $target;
    }

    /**
     * @Route(methods={"PUT"}, path="/tags/{id}")
     *
     * @OpenApi\Operation(operationId="tagEdit")
     *
     * @Deserialization(
     *     propertiesMap={"id": "id"}
     * )
     *
     * @return Tag The update tag
     */
    public function editAction(Tag $target, EntityManagerInterface $entityManager): Tag
    {
        $entityManager->flush();

        return $target;
    }

    /**
     * @Route(name="tag_get", methods={"GET"}, path="/tags/{id}")
     *
     * @OpenApi\Operation(operationId="tagGet")
     *
     * @return Tag The tag
     */
    public function getAction(Tag $target): Tag
    {
        return $target;
    }

    /**
     * @Route(methods={"DELETE"}, path="/tags/{id}")
     *
     * @OpenApi\Operation(operationId="tagDelete")
     *
     * @return void Empty response mean success
     */
    public function deleteAction(Tag $target, EntityManagerInterface $entityManager): void
    {
        $entityManager->remove($target);
        $entityManager->flush();
    }

    /**
     * @Route(methods={"GET"}, path="/tags")
     *
     * @OpenApi\Operation(operationId="tagList")
     *
     * @return Tag[] All tags
     */
    public function listAction(EntityHandler $entityHandler): array
    {
        return $entityHandler->findAll(Tag::class);
    }

    /**
     * @Route(methods={"POST"}, path="/tags/activate-all")
     *
     * @OpenApi\Operation(operationId="tagActivateAll")
     *
     * @Serialization(statusCode=204)
     *
     * @return void Empty return value mean success
     */
    public function activateAllAction(EntityHandler $entityHandler): void
    {
        foreach ($entityHandler->findBy(Tag::class, ['active' => false]) as $tag) {
            $tag->setActive(true);
        }

        $entityHandler->flush();
    }
}
