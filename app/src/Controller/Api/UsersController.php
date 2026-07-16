<?php

namespace App\Controller\Api;

use App\DTO\SimpleUser;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\Component\OpenApi\Serializer\Serialization;
use Draw\DoctrineExtra\ORM\EntityHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private EntityHandler $entityHandler,
        private MailerInterface $mailer,
    ) {
    }

    /**
     * @return User The newly created user
     */
    #[Route(path: '/users', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OpenApi\Operation(operationId: 'userCreate')]
    public function create(
        #[RequestBody]
        User $target,
    ): User {
        $this->logger->info('Create new user');

        $this->entityManager->persist($target);
        $this->entityManager->flush();

        return $target;
    }

    /**
     * @return User The currently connected user
     */
    #[Route(path: '/me', name: 'me', methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'me')]
    public function me(): User
    {
        return $this->getUser();
    }

    /**
     * Get a simple representation of the currently connected user.
     */
    #[Route(path: '/me-simple', name: 'meSimple', methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'meSimple')]
    public function meSimple(): SimpleUser
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
    public function edit(
        #[RequestBody(propertiesMap: ['id' => 'id'])]
        User $target,
    ): User {
        $this->entityManager->flush();

        return $target;
    }

    /**
     * @return array<Tag> The new list of tags
     */
    #[Route(path: '/users/{id}/tags', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OpenApi\Operation(operationId: 'userSetTags')]
    public function setTags(
        User $target,
        #[RequestBody(type: 'array<App\Entity\Tag>')]
        array $tags,
    ): array {
        $target->setTags($tags);

        $this->entityManager->flush();

        return $target->getTags()->toArray();
    }

    #[Route(path: '/users/{id}', name: 'user_get', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OpenApi\Operation(operationId: 'userGet')]
    public function getAction(User $target): User
    {
        return $target;
    }

    #[Route(path: '/users/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OpenApi\Operation(operationId: 'userDelete')]
    #[Serialization(statusCode: 204)]
    public function delete(User $target): void
    {
        $this->entityManager->remove($target);
        $this->entityManager->flush();
    }

    /**
     * Return a paginator list of users.
     *
     * @return User[] All users
     */
    #[Route(path: '/users', methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'userList')]
    public function list(): array
    {
        return $this->entityHandler->findAll(User::class);
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
        $this->mailer->send(new ForgotPasswordEmail($target->getEmail()));
    }
}
