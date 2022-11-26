<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\OpenApi\Configuration\Deserialization;
use Draw\Component\OpenApi\Configuration\Serialization;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\DoctrineExtra\ORM\EntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method user getUser()
 */
class UsersController extends AbstractController
{
    /**
     * @Route(methods={"POST"}, path="/users")
     *
     * @OpenApi\Operation(operationId="userCreate")
     *
     * @Deserialization
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @return User The newly created user
     */
    public function createAction(User $target, EntityManagerInterface $entityManager): User
    {
        $entityManager->persist($target);
        $entityManager->flush();

        return $target;
    }

    /**
     * @Route(name="me", methods={"GET"}, path="/me")
     *
     * @OpenApi\Operation(operationId="me")
     *
     * @Serialization(statusCode=204)
     *
     * @return User The currently connected user
     */
    public function meAction(): User
    {
        return $this->getUser();
    }

    /**
     * @Route(methods={"PUT"}, path="/users/{id}")
     *
     * @OpenApi\Operation(operationId="userEdit")
     *
     * @Deserialization(
     *     propertiesMap={"id": "id"}
     * )
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @return User The update user
     */
    public function editAction(User $target, EntityManagerInterface $entityManager): User
    {
        $entityManager->flush();

        return $target;
    }

    /**
     * @Route(methods={"PUT"}, path="/users/{id}/tags")
     *
     * @OpenApi\Operation(operationId="userSetTags")
     *
     * @Deserialization(name="tags", class="array<App\Entity\Tag>")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @return array<Tag> The new list of tags
     */
    public function setTagsAction(User $target, array $tags): array
    {
        $target->setTags($tags);

        return $target->getTags()->toArray();
    }

    /**
     * @Route(name="user_get", methods={"GET"}, path="/users/{id}")
     *
     * @OpenApi\Operation(operationId="userGet")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @return User The user
     */
    public function getAction(User $target): User
    {
        return $target;
    }

    /**
     * @Route(name="user_delete", methods={"DELETE"}, path="/users/{id}")
     *
     * @OpenApi\Operation(operationId="userDelete")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Serialization(statusCode=204)
     *
     * @return void Empty response mean success
     */
    public function deleteAction(User $target, EntityManagerInterface $entityManager): void
    {
        $entityManager->remove($target);
        $entityManager->flush();
    }

    /**
     * Return a paginator list of users.
     *
     * @Route(methods={"GET"}, path="/users")
     *
     * @OpenApi\Operation(operationId="userList")
     *
     * @return User[] All users
     */
    public function listAction(EntityHandler $entityHandler): array
    {
        return $entityHandler->findAll(User::class);
    }

    /**
     * Send a reset password email to the user.
     *
     * @Route(methods={"POST"}, path="/users/{id}/reset-password-email")
     *
     * @OpenApi\Operation(operationId="userSendResetPasswordEmail")
     *
     * @return void No return value mean email has been sent
     */
    public function sendResetPasswordEmail(User $target): void
    {
    }
}
