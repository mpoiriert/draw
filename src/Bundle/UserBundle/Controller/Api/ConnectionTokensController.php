<?php

namespace Draw\Bundle\UserBundle\Controller\Api;

use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use Draw\Bundle\DashboardBundle\Client\FeedbackNotifier;
use Draw\Bundle\DashboardBundle\Feedback\DefaultHeader;
use Draw\Bundle\DashboardBundle\Feedback\SignedIn;
use Draw\Bundle\DashboardBundle\Feedback\SignedOut;
use Draw\Bundle\OpenApiBundle\Request\Deserialization;
use Draw\Bundle\OpenApiBundle\Response\Serialization;
use Draw\Bundle\UserBundle\DTO\ConnectionToken;
use Draw\Bundle\UserBundle\DTO\Credential;
use Draw\Bundle\UserBundle\Jwt\JwtAuthenticator;
use Draw\Component\OpenApi\Schema as OpenApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ConnectionTokensController extends AbstractController
{
    /**
     * Create a token base on the username/password of a user.
     *
     * The token returned is a JWT token (https://jwt.io/).
     * Once you have a token you can pass it as a Authorization Bearer request header: (Authorization: Bearer **token**).
     * If you decode the token you can read the **exp** attribute and see until when it's valid. Before the expiration
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
     * @Dashboard\ActionCreate(
     *     button=@Dashboard\Button\Button(label="_drawUserBundle.connect"),
     *     dialog=true,
     *     templates={
     *       "notification_save":"{{ '_drawUserBundle.notification.connect'|trans({}, 'DrawDashboardBundle')|raw }}"
     *     }
     * )
     *
     * @Dashboard\Breadcrumb(label="_drawUserBundle.breadcrumb.createConnectionToken")
     *
     * @Security("not is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param FeedbackNotifier $feedbackNotifier
     *
     * @return ConnectionToken The newly created token
     */
    public function createAction(
        Credential $credential,
        UserProviderInterface $userProvider,
        JwtAuthenticator $authenticator,
        UserPasswordEncoderInterface $passwordEncoder,
        ?FeedbackNotifier $feedbackNotifier
    ) {
        $user = $userProvider->loadUserByUsername($credential->getUsername());

        if (is_null($user)) {
            throw new HttpException(400, 'User not found');
        }

        if (!$passwordEncoder->isPasswordValid($user, $credential->getPassword())) {
            throw new HttpException(403, 'Invalid credential');
        }

        $connectionToken = new ConnectionToken($authenticator->encode($user));

        if ($feedbackNotifier) {
            $feedbackNotifier->sendFeedback(new DefaultHeader('Authorization', 'Bearer '.$connectionToken->token));
            $feedbackNotifier->sendFeedback(new SignedIn());
        }

        return $connectionToken;
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
    public function refreshAction(JwtAuthenticator $authenticator)
    {
        return new ConnectionToken($authenticator->encode($this->getUser()));
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
     * @Dashboard\ActionDelete(
     *     button=@Dashboard\Button\Button(label="_drawUserBundle.disconnect", style="icon-button", icon="exit_to_app"),
     *     flow=@Dashboard\ConfirmFlow(message="_drawUserBundle.confirm_disconnect")
     * )
     *
     * @param FeedbackNotifier $feedbackNotifier
     *
     * @return void Nothing to be returned
     */
    public function clearAction(?FeedbackNotifier $feedbackNotifier)
    {
        if ($feedbackNotifier) {
            $feedbackNotifier->sendFeedback(new DefaultHeader('Authorization', '', true));
            $feedbackNotifier->sendFeedback(new SignedOut());
        }
    }
}
