<?php namespace App\Controller\Api;

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

class TagsController
{
    /**
     * @Route(methods={"POST"}, path="/tags")
     *
     * @OpenApi\Operation(operationId="tagCreate")
     *
     * @Deserialization(name="tag")
     *
     * @Dashboard\ActionCreate()
     *
     * @Serialization(statusCode=201)
     *
     * @param Tag $tag
     * @param EntityManagerInterface $entityManager
     * @return Tag The newly created tag
     */
    public function createAction(Tag $tag, EntityManagerInterface $entityManager)
    {
        $entityManager->persist($tag);
        $entityManager->flush();
        return $tag;
    }

    /**
     * @Route(methods={"DELETE"}, path="/tags/{id}")
     *
     * @OpenApi\Operation(operationId="tagDelete")
     *
     * @Dashboard\ActionDelete(
     *     targets={Tag::class},
     *     flow=@Dashboard\ConfirmFlow(message="Are you sure you want to delete the tag {{tag.label}} ?")
     * )
     *
     * @param Tag $tag
     * @param EntityManagerInterface $entityManager
     * @return void No result mean a success
     */
    public function deleteAction(Tag $tag, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($tag);
        $entityManager->flush();
    }

    /**
     * @Route(methods={"GET"}, path="/tags")
     *
     * @OpenApi\Operation(operationId="tagList")
     *
     * @Dashboard\ActionList(paginated=true)
     *
     * @param Request $request
     * @param PaginatorBuilder $paginatorBuilder
     *
     * @return Paginator<Tag> Tags Paginator
     */
    public function listAction(Request $request, PaginatorBuilder $paginatorBuilder)
    {
        return $paginatorBuilder->fromRequest(Tag::class, $request);
    }
}