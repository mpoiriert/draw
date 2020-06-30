<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\DashboardBundle\Action\ActionFinder;
use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use Draw\Bundle\DashboardBundle\Client\FeedbackNotifier;
use Draw\Bundle\DashboardBundle\Doctrine\Paginator;
use Draw\Bundle\DashboardBundle\Doctrine\PaginatorBuilder;
use Draw\Bundle\DashboardBundle\Feedback\Notification;
use Draw\Bundle\OpenApiBundle\Request\Deserialization;
use Draw\Bundle\OpenApiBundle\Response\Serialization;
use Draw\Component\OpenApi\Schema as OpenApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Dashboard\Breadcrumb(parentOperationId="userList")
 * @Dashboard\Targets({User::class})
 *
 * @method user getUser()
 */
class UsersController extends AbstractController
{
    /**
     * @Route(methods={"POST"}, path="/users")
     *
     * @OpenApi\Operation(operationId="userCreate")
     *
     * @Deserialization()
     *
     * @Dashboard\ActionCreate()
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @return User The newly created user
     */
    public function createAction(User $target, EntityManagerInterface $entityManager)
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
     * @Dashboard\Action(
     *     targets={},
     *     button=@Dashboard\Button\Button(id="me", icon="account_circle", behaviours={"navigateTo-userEdit"})
     * )
     *
     * @return User The currently connected user
     */
    public function meAction(FeedbackNotifier $notifier, ActionFinder $actionFinder)
    {
        return $this->getUser();
    }

    /**
     * @Route(methods={"PUT"}, path="/users/{id}")
     *
     * @OpenApi\Operation(operationId="userEdit")
     *
     * @Deserialization(
     *     propertiesMap={"id":"id"}
     * )
     *
     * @Dashboard\ActionEdit()
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @return User The update user
     */
    public function editAction(User $target, EntityManagerInterface $entityManager)
    {
        $entityManager->flush();

        return $target;
    }

    /**
     * @Route(name="user_get", methods={"GET"}, path="/users/{id}")
     *
     * @OpenApi\Operation(operationId="userGet")
     *
     * @Dashboard\ActionShow()
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @return User The user
     */
    public function getAction(User $target)
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
     * @Dashboard\ActionDelete(
     *     flow=@Dashboard\ConfirmFlow(message="Are you sure you want to delete the user {{target.email}} ?")
     * )
     *
     * @Serialization(statusCode=204)
     *
     * @return void Empty response mean success
     */
    public function deleteAction(User $target, EntityManagerInterface $entityManager)
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
     * @Dashboard\ActionList(
     *     title="_action.userList.title"
     * )
     *
     * @Dashboard\Breadcrumb()
     *
     * @return Paginator<User> A paginated user list
     */
    public function listAction(PaginatorBuilder $paginatorBuilder, Request $request)
    {
        return $paginatorBuilder
            ->fromClass(User::class)
            ->extractFromRequest($request)
            ->build();
    }

    /**
     * Send a reset password email to the user.
     *
     * @Route(methods={"POST"}, path="/users/{id}/reset-password-email")
     *
     * @OpenApi\Operation(operationId="userSendResetPasswordEmail")
     *
     * @Dashboard\Action(
     *     isInstanceTarget=true,
     *     button=@Dashboard\Button\Button(label="Send forgot password email", icon="email")
     * )
     *
     * @return void No return value mean email has been sent
     */
    public function sendResetPasswordEmail(User $target, FeedbackNotifier $notifier)
    {
        $notifier->sendFeedback(new Notification('success', 'Email sent! (not really)'));
    }
}
