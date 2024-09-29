<?php

namespace Draw\Bundle\SonataExtraBundle\Controller;

use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Model\AuditManagerInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Util\AdminAclUserManagerInterface;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Sonata\Exporter\Exporter;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

trait ControllerTrait
{
    protected AdminInterface $admin;

    public static function getSubscribedServices(): array
    {
        return [
            'sonata.admin.pool' => Pool::class,
            'sonata.admin.audit.manager' => AuditManagerInterface::class,
            'sonata.admin.object.manipulator.acl.admin' => AdminObjectAclManipulator::class,
            'sonata.admin.request.fetcher' => AdminFetcherInterface::class,
            'sonata.exporter.exporter' => '?'.Exporter::class,
            'sonata.admin.admin_exporter' => '?'.AdminExporter::class,
            'sonata.admin.security.acl_user_manager' => '?'.AdminAclUserManagerInterface::class,
            'logger' => '?'.LoggerInterface::class,
            'translator' => TranslatorInterface::class,
            'router' => '?'.RouterInterface::class,
            'request_stack' => '?'.RequestStack::class,
            'http_kernel' => '?'.HttpKernelInterface::class,
            'session' => '?'.SessionInterface::class,
            'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
            'twig' => '?'.Environment::class,
            'form.factory' => '?'.FormFactoryInterface::class,
            'security.token_storage' => '?'.TokenStorageInterface::class,
            'security.csrf.token_manager' => '?'.CsrfTokenManagerInterface::class,
            'parameter_bag' => '?'.ContainerBagInterface::class,
            'controller_resolver' => 'controller_resolver',
        ];
    }

    public function configureAdmin(AdminInterface $admin): void
    {
        $this->admin = $admin;
    }

    protected function validateCsrfToken(Request $request, string $intention): void
    {
        if (!$this->container->has('security.csrf.token_manager')) {
            return;
        }

        $valid = $this->container->get('security.csrf.token_manager')
            ->isTokenValid(new CsrfToken($intention, $request->get('_sonata_csrf_token')));

        if (!$valid) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The csrf token is not valid, CSRF attack?');
        }
    }

    final protected function getCsrfToken(string $intention): ?string
    {
        if (!$this->container->has('security.csrf.token_manager')) {
            return null;
        }

        return $this->container->get('security.csrf.token_manager')->getToken($intention)->getValue();
    }

    final protected function isXmlHttpRequest(Request $request): bool
    {
        return $request->isXmlHttpRequest()
            || $request->request->getBoolean('_xml_http_request')
            || $request->query->getBoolean('_xml_http_request');
    }

    protected function getBaseTemplate(): string
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        return $this->admin
            ->getTemplateRegistry()
            ->getTemplate($this->isXmlHttpRequest($request) ? 'ajax' : 'layout');
    }

    final protected function trans(
        string $id,
        array $parameters = [],
        ?string $domain = null,
        ?string $locale = null,
    ): string {
        return $this->container->get('translator')
            ->trans(
                $id,
                $parameters,
                $domain ?? $this->admin->getTranslationDomain(),
                $locale
            );
    }

    final protected function redirectToList(): RedirectResponse
    {
        $filter = $this->admin->getFilterParameters();

        return $this->redirect(
            $this->admin->generateUrl(
                'list',
                $filter ? ['filter' => $filter] : []
            )
        );
    }

    /**
     * Sets the admin form theme to form view. Used for compatibility between Symfony versions.
     *
     * @param string[]|null $theme
     */
    final protected function setFormTheme(FormView $formView, ?array $theme = null): void
    {
        $this->container
            ->get('twig')
            ->getRuntime(FormRenderer::class)
            ->setTheme($formView, $theme);
    }

    final protected function renderWithExtraParams(
        string $view,
        array $parameters = [],
        ?Response $response = null,
    ): Response {
        return $this->render($view, $this->addRenderExtraParams($parameters), $response);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    protected function addRenderExtraParams(array $parameters = []): array
    {
        $parameters['admin'] ??= $this->admin;
        $parameters['base_template'] ??= $this->getBaseTemplate();

        return $parameters;
    }
}
