<?php

namespace Draw\Bundle\UserBundle\Controller\Api;

use Draw\Bundle\UserBundle\DTO\ConnectionToken;
use Draw\Bundle\UserBundle\DTO\Credential;
use Draw\Component\OpenApi\Configuration\Deserialization;
use Draw\Component\OpenApi\Configuration\Serialization;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
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
     *
     * @OpenApi\Operation(
     *     tags="Security",
     *     operationId="drawUserBundleCreateConnectionToken"
     * )
     *
     * @Deserialization(name="credential")
     *
     * @Serialization(statusCode=201)
     *
     * @Security("not is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return ConnectionToken The newly created token
     */
    public function createAction(
        Credential $credential,
        UserProviderInterface $userProvider,
        JwtAuthenticator $authenticator,
        UserPasswordHasherInterface $passwordEncoder
    ): ConnectionToken {
        $user = $userProvider->loadUserByIdentifier($credential->getUsername());

        if (null === $user) {
            throw new HttpException(400, 'User not found');
        }

        if (!$passwordEncoder->isPasswordValid($user, $credential->getPassword())) {
            throw new HttpException(403, 'Invalid credential');
        }

        return new ConnectionToken($authenticator->generaToken($user));
    }

    /**
     * @Route(name="drawUserBundle_connection_token_refresh", methods={"POST"}, path="/connection-tokens/refresh")
     *
     * @OpenApi\Operation(
     *     tags="Security",
     *     operationId="drawUserBundleRefreshConnectionToken"
     * )
     *
     * @Serialization(statusCode=200)
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     *
     * @return ConnectionToken The refreshed token
     */
    public function refreshAction(JwtAuthenticator $authenticator): ConnectionToken
    {
        return new ConnectionToken($authenticator->generaToken($this->getUser()));
    }

    /**
     * @Route(name="drawUserBundle_connection_clear", methods={"DELETE"}, path="/connection-tokens/current")
     *
     * @OpenApi\Operation(
     *     tags="Security",
     *     operationId="drawUserBundleDeleteConnectionToken"
     * )
     *
     * @Serialization(statusCode=204)
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     *
     * @return void Nothing to be returned
     */
    public function clearAction(): void
    {
    }
}
