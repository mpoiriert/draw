<?php namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use Draw\Bundle\DashboardBundle\Client\FeedbackNotifier;
use Draw\Bundle\DashboardBundle\Doctrine\Paginator;
use Draw\Bundle\DashboardBundle\Doctrine\PaginatorBuilder;
use Draw\Bundle\OpenApiBundle\Request\Deserialization;
use Draw\Bundle\OpenApiBundle\Response\Serialization;
use Draw\Component\OpenApi\Schema as OpenApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    /**
     * @Route(methods={"POST"}, path="/users")
     *
     * @OpenApi\Operation(operationId="userCreate")
     *
     * @Deserialization(name="user")
     *
     * @Dashboard\ActionCreate()
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return User The newly created user
     */
    public function createAction(User $user, EntityManagerInterface $entityManager)
    {
        $entityManager->persist($user);
        $entityManager->flush();
        return $user;
    }

    /**
     * @Route(methods={"PUT"}, path="/users/{id}")
     *
     * @OpenApi\Operation(operationId="userEdit")
     *
     * @Deserialization(
     *     name="user",
     *     propertiesMap={"id":"id"}
     * )
     *
     * @Dashboard\ActionEdit(
     *   targets={User::class}
     * )
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return User The update user
     */
    public function editAction(User $user, EntityManagerInterface $entityManager)
    {
        $entityManager->flush();
        return $user;
    }

    /**
     * @Route(name="user_get", methods={"GET"}, path="/users/{id}")
     *
     * @OpenApi\Operation(operationId="userGet")
     *
     * @Dashboard\ActionShow(
     *     targets={User::class}
     * )
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     *
     * @return User The user
     */
    public function getAction(User $user)
    {
        return $user;
    }

    /**
     * @Route(name="user_delete", methods={"DELETE"}, path="/users/{id}")
     *
     * @OpenApi\Operation(operationId="userDelete")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Dashboard\ActionDelete(
     *     targets={User::class},
     *     flow=@Dashboard\ConfirmFlow(message="Are you sure you want to delete the user {{user.email}} ?")
     * )
     *
     * @Serialization(statusCode=204)
     */
    public function deleteAction(User $user, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($user);
        $entityManager->flush();
    }

    /**
     * Return a paginator list of users
     *
     * @Route(methods={"GET"}, path="/users")
     *
     * @OpenApi\Operation(operationId="userList")
     *
     * @Dashboard\ActionList()
     *
     * @param PaginatorBuilder $paginatorBuilder
     * @param Request $request
     *
     * @return Paginator<User> A paginated user list
     */
    public function listAction(PaginatorBuilder $paginatorBuilder, Request $request)
    {
        return $paginatorBuilder->fromRequest(User::class, $request);
    }

    /**
     * Send a reset password email to the user
     *
     * @Route(methods={"POST"}, path="/users/{id}/reset-password-email")
     *
     * @OpenApi\Operation(operationId="userSendResetPasswordEmail")
     *
     * @Dashboard\Action(
     *     targets={User::class},
     *     button=@Dashboard\Button(label="Send forgot password email", icon="email")
     * )
     *
     * @param User $user
     * @param FeedbackNotifier $notifier
     *
     * @return void No return value mean email has been sent
     */
    public function sendResetPasswordEmail(User $user, FeedbackNotifier $notifier)
    {
        $notifier->sendFeedback(new Notification('success', 'Email sent! (not really)'));
    }
}