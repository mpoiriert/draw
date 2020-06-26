<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use Draw\Bundle\DashboardBundle\Doctrine\Paginator;
use Draw\Bundle\DashboardBundle\Doctrine\PaginatorBuilder;
use Draw\Bundle\OpenApiBundle\Request\Deserialization;
use Draw\Bundle\OpenApiBundle\Response\Serialization;
use Draw\Component\OpenApi\Schema as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Dashboard\Targets({Tag::class})
 */
class TagsController
{
    /**
     * @Route(methods={"POST"}, path="/tags")
     *
     * @OpenApi\Operation(operationId="tagCreate")
     *
     * @Deserialization()
     *
     * @Dashboard\ActionCreate()
     *
     * @Serialization(statusCode=201)
     *
     * @return Tag The newly created tag
     */
    public function createAction(Tag $target, EntityManagerInterface $entityManager)
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
     *     propertiesMap={"id":"id"}
     * )
     *
     * @Dashboard\ActionEdit()
     *
     * @return Tag The update tag
     */
    public function editAction(Tag $target, EntityManagerInterface $entityManager)
    {
        $entityManager->flush();

        return $target;
    }

    /**
     * @Route(name="tag_get", methods={"GET"}, path="/tags/{id}")
     *
     * @OpenApi\Operation(operationId="tagGet")
     *
     * @Dashboard\ActionShow()
     *
     * @return Tag The tag
     */
    public function getAction(Tag $target)
    {
        return $target;
    }

    /**
     * @Route(methods={"DELETE"}, path="/tags/{id}")
     *
     * @OpenApi\Operation(operationId="tagDelete")
     *
     * @Dashboard\ActionDelete(
     *     flow=@Dashboard\ConfirmFlow(message="Are you sure you want to delete the tag {{target.label}} ?")
     * )
     *
     * @return void Empty response mean success
     */
    public function deleteAction(Tag $target, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($target);
        $entityManager->flush();
    }

    /**
     * @Route(methods={"GET"}, path="/tags")
     *
     * @OpenApi\Operation(operationId="tagList")
     *
     * @Dashboard\ActionList()
     *
     * @return Paginator<Tag> Tags Paginator
     */
    public function listAction(Request $request, PaginatorBuilder $paginatorBuilder)
    {
        return $paginatorBuilder->fromRequest(Tag::class, $request);
    }

    /**
     * @Route(methods={"POST"}, path="/tags/activate-all")
     *
     * @OpenApi\Operation(operationId="tagActivateAll")
     *
     * @Dashboard\Action(
     *     isInstanceTarget=false,
     *     button=@Dashboard\Button\Button(id="activateAll", label="activateAll", behaviours={"navigateTo-tagList"})
     * )
     *
     * @Serialization(statusCode=204)
     *
     * @return void Empty return value mean success
     */
    public function activateAllAction(EntityManagerInterface $entityManager)
    {
        $tags = $entityManager->getRepository(Tag::class)->findBy(['active' => false]);
        foreach ($tags as $tag) {
            $tag->setActive(true);
        }

        $entityManager->flush();
    }
}
