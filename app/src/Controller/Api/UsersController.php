<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\Component\OpenApi\Serializer\Serialization;
use Draw\DoctrineExtra\ORM\EntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @IsGranted("ROLE_ADMIN")
     *
     * @return User The newly created user
     */
    #[OpenApi\Operation(operationId: 'userCreate')]
    public function createAction(
        #[RequestBody] User $target,
        EntityManagerInterface $entityManager
    ): User {
        $entityManager->persist($target);
        $entityManager->flush();

        return $target;
    }

    /**
     * @Route(name="me", methods={"GET"}, path="/me")
     *
     * @return User The currently connected user
     */
    #[OpenApi\Operation(operationId: 'me')]
    public function meAction(): User
    {
        return $this->getUser();
    }

    /**
     * @Route(methods={"PUT"}, path="/users/{id}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @return User The update user
     */
    #[OpenApi\Operation(operationId: 'userEdit')]
    public function editAction(
        #[RequestBody(propertiesMap: ['id' => 'id'])] User $target,
        EntityManagerInterface $entityManager
    ): User {
        $entityManager->flush();

        return $target;
    }

    /**
     * @Route(methods={"PUT"}, path="/users/{id}/tags")
     * @IsGranted("ROLE_ADMIN")
     *
     * @ParamConverter("target", class=User::class, converter="doctrine.orm")
     *
     * @return array<Tag> The new list of tags
     */
    #[OpenApi\Operation(operationId: 'userSetTags')]
    public function setTagsAction(
        User $target,
        #[RequestBody(type: 'array<App\Entity\Tag>')] array $tags,
        EntityManagerInterface $entityManager
    ): array {
        $target->setTags($tags);

        $entityManager->flush();

        return $target->getTags()->toArray();
    }

    /**
     * @Route(name="user_get", methods={"GET"}, path="/users/{id}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @ParamConverter("target", class=User::class, converter="doctrine.orm")
     *
     * @return User The user
     */
    #[OpenApi\Operation(operationId: 'userGet')]
    public function getAction(User $target): User
    {
        return $target;
    }

    /**
     * @Route(name="user_delete", methods={"DELETE"}, path="/users/{id}")
     * @IsGranted("ROLE_ADMIN")
     *
     * @ParamConverter("target", class=User::class, converter="doctrine.orm")
     *
     * @return void Empty response mean success
     */
    #[OpenApi\Operation(operationId: 'userDelete')]
    #[Serialization(statusCode: 204)]
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
     * @return User[] All users
     */
    #[OpenApi\Operation(operationId: 'userList')]
    public function listAction(EntityHandler $entityHandler): array
    {
        return $entityHandler->findAll(User::class);
    }

    /**
     * Send a reset password email to the user.
     *
     * @Route(methods={"POST"}, path="/users/{id}/reset-password-email")
     *
     * @return void No return value mean email has been sent
     */
    #[OpenApi\Operation(operationId: 'userSendResetPasswordEmail')]
    public function sendResetPasswordEmail(User $target): void
    {
    }
}
