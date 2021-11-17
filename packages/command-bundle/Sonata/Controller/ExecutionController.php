<?php

namespace Draw\Bundle\CommandBundle\Sonata\Controller;

use Draw\Bundle\CommandBundle\CommandRegistry;
use Draw\Bundle\CommandBundle\Entity\Execution;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExecutionController extends CRUDController
{
    /**
     * @var CommandRegistry
     */
    private $commandFactory;

    /**
     * @required
     */
    public function setCommandFactory(CommandRegistry $commandFactory)
    {
        $this->commandFactory = $commandFactory;
    }

    public function myCreateAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        if (!$request->get('command') && $request->isMethod('get')) {
            return $this->renderWithExtraParams(
                '@DrawCommand/ExecutionAdmin/select_command.html.twig',
                [
                    'commands' => $this->commandFactory->getCommands(),
                    'action' => 'create',
                ]
            );
        }

        return parent::createAction($request);
    }

    public function acknowledgeAction(Request $request, Execution $execution): Response
    {
        $execution->setState(Execution::STATE_ACKNOWLEDGE);
        $this->admin->getModelManager()->update($execution);

        $this->addFlash(
            'sonata_flash_success',
            $this->trans(
                'flash_edit_success',
                ['%name%' => $this->escapeHtml($this->admin->toString($execution))],
                'SonataAdminBundle'
            )
        );

        // TODO drop when stop supporting sonata 3.x
        if ((new \ReflectionObject($this))->getMethod('redirectTo')->getNumberOfParameters() > 1) {
            return $this->redirectTo($request, $execution);
        }

        return $this->redirectTo($execution);
    }
}
