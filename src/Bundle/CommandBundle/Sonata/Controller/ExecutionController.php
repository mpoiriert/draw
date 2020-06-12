<?php

namespace Draw\Bundle\CommandBundle\Sonata\Controller;

use Draw\Bundle\CommandBundle\CommandRegistry;
use Draw\Bundle\CommandBundle\Entity\Execution;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request = null)
    {
        $this->admin->checkAccess('create');

        if (!$request->get('command') && $request->isMethod('get')) {
            return $this->renderWithExtraParams(
                '@DrawSonataCommand/ExecutionAdmin/select_command.html.twig',
                [
                    'commands' => $this->commandFactory->getCommands(),
                    'action' => 'create',
                ]
            );
        }

        return parent::createAction();
    }

    public function acknowledgeAction(Execution $execution)
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

        return $this->redirectTo($execution);
    }
}
