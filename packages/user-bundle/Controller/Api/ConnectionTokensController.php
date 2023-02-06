<?php

namespace Draw\Bundle\UserBundle\Controller\Api;

use Draw\Bundle\UserBundle\DTO\ConnectionToken;
use Draw\Bundle\UserBundle\DTO\Credential;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\Component\OpenApi\Serializer\Serialization;
use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

// todo refactor to be reusable
class ConnectionTokensController extends AbstractController
{
    /**
     * Create a token base on the username/password of a user.
     *
     * The token returned is a JWT token (https://jwt.io/).
     * Once you have a token you can pass it as a Authorization Bearer request header: (Authorization: Bearer **token**).
     * If you decode the token you can read the **exp** attribute and see until when it's valid. Before the expiration
     * is reach you should call the POST /api/connection-tokens endpoint to get a new one.
     *
     * @Route(name="connection_token_create", methods={"POST"}, path="/connection-tokens")
     * @Security("not is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return ConnectionToken The newly created token
     */
    #[OpenApi\Operation(operationId: 'drawUserBundleCreateConnectionToken', tags: ['Security'])]
    #[Serialization(statusCode: 201)]
    public function createAction(
        #[RequestBody] Credential $credential,
        UserProviderInterface $userProvider,
        JwtAuthenticator $authenticator,
        UserPasswordHasherInterface $passwordEncoder
    ): ConnectionToken {
        try {
            $user = $userProvider->loadUserByIdentifier($credential->getUsername());
        } catch (UserNotFoundException) {
            throw new HttpException(400, 'User not found');
        }

        if (
            !$user instanceof PasswordAuthenticatedUserInterface
            || !$passwordEncoder->isPasswordValid($user, $credential->getPassword())
        ) {
            throw new HttpException(403, 'Invalid credential');
        }

        return new ConnectionToken($authenticator->generaToken($user));
    }

    /**
     * @Route(name="drawUserBundle_connection_token_refresh", methods={"POST"}, path="/connection-tokens/refresh")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     *
     * @return ConnectionToken The refreshed token
     */
    #[OpenApi\Operation(operationId: 'drawUserBundleRefreshConnectionToken', tags: ['Security'])]
    public function refreshAction(JwtAuthenticator $authenticator): ConnectionToken
    {
        return new ConnectionToken($authenticator->generaToken($this->getUser()));
    }

    /**
     * @Route(name="drawUserBundle_connection_clear", methods={"DELETE"}, path="/connection-tokens/current")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     *
     * @return void Nothing to be returned
     */
    #[OpenApi\Operation(operationId: 'drawUserBundleDeleteConnectionToken', tags: ['Security'])]
    #[Serialization(statusCode: 204)]
    public function clearAction(): void
    {
    }
}
