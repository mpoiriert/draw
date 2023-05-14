<?php

namespace App\Controller\Api;

use App\DTO\SimpleUser;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\Component\OpenApi\Serializer\Serialization;
use Draw\DoctrineExtra\ORM\EntityHandler;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method user getUser()
 */
#[AutoconfigureTag(
    'logger.decorate',
    attributes: [
        'message' => '[UsersController] {message}',
        'service' => 'UsersController',
    ]
)]
class UsersController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @return User The newly created user
     */
    #[Route(path: '/users', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OpenApi\Operation(operationId: 'userCreate')]
    public function createAction(
        #[RequestBody] User $target,
        EntityManagerInterface $entityManager
    ): User {
        $this->logger->info('Create new user');

        $entityManager->persist($target);
        $entityManager->flush();

        return $target;
    }

    /**
     * @return User The currently connected user
     */
    #[Route(path: '/me', name: 'me', methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'me')]
    public function meAction(): User
    {
        return $this->getUser();
    }

    /**
     * Get a simple representation of the currently connected user.
     */
    #[Route(path: '/me-simple', name: 'meSimple', methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'meSimple')]
    public function meSimpleAction(): SimpleUser
    {
        return new SimpleUser($this->getUser());
    }

    /**
     * @return User The update user
     */
    #[Route(path: '/users/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OpenApi\Operation(operationId: 'userEdit')]
    #[OpenApi\PathParameter(name: 'id', description: 'The user id to edit', type: 'integer')]
    public function editAction(
        #[RequestBody(propertiesMap: ['id' => 'id'])] User $target,
        EntityManagerInterface $entityManager
    ): User {
        $entityManager->flush();

        return $target;
    }

    /**
     * @return array<Tag> The new list of tags
     */
    #[Route(path: '/users/{id}/tags', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OpenApi\Operation(operationId: 'userSetTags')]
    #[Entity('target', class: User::class)]
    public function setTagsAction(
        User $target,
        #[RequestBody(type: 'array<App\Entity\Tag>')] array $tags,
        EntityManagerInterface $entityManager
    ): array {
        $target->setTags($tags);

        $entityManager->flush();

        return $target->getTags()->toArray();
    }

    #[Route(path: '/users/{id}', name: 'user_get', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[Entity('target', class: User::class)]
    #[OpenApi\Operation(operationId: 'userGet')]
    public function getAction(User $target): User
    {
        return $target;
    }

    #[Route(path: '/users/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[Entity('target', class: User::class)]
    #[IsGranted('ROLE_ADMIN')]
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
     * @return User[] All users
     */
    #[Route(path: '/users', methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'userList')]
    public function listAction(EntityHandler $entityHandler): array
    {
        return $entityHandler->findAll(User::class);
    }

    /**
     * Send a reset password email to the user.
     *
     * @return void No return value mean email has been sent
     */
    #[Route(path: '/users/{id}/reset-password-email', methods: ['POST'])]
    #[OpenApi\Operation(operationId: 'userSendResetPasswordEmail')]
    public function sendResetPasswordEmail(User $target): void
    {
    }
}
