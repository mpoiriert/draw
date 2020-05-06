<?php namespace App\Controller\Api;

use App\Entity\User;
use App\DTO\ConnectionToken;
use App\DTO\Credential;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use Draw\Bundle\DashboardBundle\Client\FeedbackNotifier;
use Draw\Bundle\DashboardBundle\Feedback\DefaultHeader;
use Draw\Bundle\DashboardBundle\Feedback\SignedIn;
use Draw\Bundle\DashboardBundle\Feedback\SignedOut;
use Draw\Bundle\OpenApiBundle\Request\Deserialization;
use Draw\Bundle\OpenApiBundle\Response\Serialization;
use Draw\Bundle\UserBundle\Jwt\JwtAuthenticator;
use Draw\Component\OpenApi\Schema as OpenApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method User getUser()
 */
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
     *     operationId="createConnectionToken"
     * )
     *
     * @Deserialization(name="credential")
     *
     * @Serialization(statusCode=201)
     *
     * @Dashboard\ActionCreate(
     *     button=@Dashboard\Button(label="Connect", icon="power_settings_new"),
     *     dialog=true,
     *     templates={
     *       "notification_save":"{{ 'notification.connect'|trans({}, 'DrawDashboardBundle')|raw }}"
     *     }
     * )
     *
     * @Security("not is_granted('ROLE_USER')")
     *
     * @param Credential $credential
     * @param EntityManagerInterface $entityManager
     * @param JwtAuthenticator $authenticator
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param FeedbackNotifier $feedbackNotifier
     *
     * @return ConnectionToken The newly created token
     */
    public function createAction(
        Credential $credential,
        EntityManagerInterface $entityManager,
        JwtAuthenticator $authenticator,
        UserPasswordEncoderInterface $passwordEncoder,
        FeedbackNotifier $feedbackNotifier
    ) {

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $credential->getUsername()]);

        if (is_null($user)) {
            throw new HttpException(400, 'User not found');
        }

        if (!$passwordEncoder->isPasswordValid($user, $credential->getPassword())) {
            throw new HttpException(403, 'Invalid credential');
        }

        $connectionToken = new ConnectionToken($authenticator->encode($user));

        $feedbackNotifier->sendFeedback(new DefaultHeader('Authorization', 'Bearer ' . $connectionToken->token));
        $feedbackNotifier->sendFeedback(new SignedIn());

        return $connectionToken;
    }

    /**
     * @Route(name="connection_token_refresh", methods={"POST"}, path="/connection-tokens/refresh")
     *
     * @OpenApi\Operation(
     *     tags="Security",
     *     operationId="refreshConnectionToken"
     * )
     *
     * @Serialization(statusCode=200)
     *
     * @IsGranted("ROLE_USER")
     *
     * @param JwtAuthenticator $authenticator
     *
     * @return ConnectionToken The refreshed token
     */
    public function refreshAction(JwtAuthenticator $authenticator)
    {
        return new ConnectionToken($authenticator->encode($this->getUser()));
    }

    /**
     * @Route(name="connection_clear", methods={"DELETE"}, path="/connection-tokens/current")
     *
     * @OpenApi\Operation(
     *     tags="Security",
     *     operationId="deleteConnectionToken"
     * )
     *
     * @Serialization(statusCode=204)
     *
     * @IsGranted("ROLE_USER")
     *
     * @Dashboard\ActionDelete(
     *     button=@Dashboard\Button(label="Disconnect", icon="exit_to_app"),
     *     flow=@Dashboard\ConfirmFlow(message="Are you sure ?")
     * )
     *
     * @param FeedbackNotifier $feedbackNotifier
     *
     * @return void Nothing to be returned
     */
    public function clearAction(FeedbackNotifier $feedbackNotifier)
    {
        $feedbackNotifier->sendFeedback(new DefaultHeader('Authorization', '', true));
        $feedbackNotifier->sendFeedback(new SignedOut());
    }
}