<?php

namespace Draw\Bundle\SonataIntegrationBundle\Console\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\SonataIntegrationBundle\Console\CommandRegistry;
use Draw\Component\Console\Entity\Execution;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExecutionController extends CRUDController
{
    private CommandRegistry $commandFactory;

    /**
     * @required
     */
    public function inject(CommandRegistry $commandFactory): void
    {
        $this->commandFactory = $commandFactory;
    }

    public function myCreateAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        if (!$request->get('command') && $request->isMethod('get')) {
            return $this->renderWithExtraParams(
                '@DrawSonataIntegration/Console/Execution/select_command.html.twig',
                [
                    'commands' => $this->commandFactory->getCommands(),
                    'action' => 'create',
                ]
            );
        }

        return parent::createAction($request);
    }

    #[Entity('execution', class: Execution::class)]
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

        return $this->redirectTo($request, $execution);
    }

    public function reportAction(EntityManagerInterface $entityManager): Response
    {
        $stats = $entityManager->createQueryBuilder()
            ->from(Execution::class, 'execution')
            ->addSelect('execution.autoAcknowledgeReason as reason')
            ->addSelect('COUNT(execution.id) as count')
            ->andWhere('execution.autoAcknowledgeReason IS NOT NULL')
            ->andWhere('execution.state = :state')
            ->setParameter('state', Execution::STATE_AUTO_ACKNOWLEDGE)
            ->groupBy('execution.autoAcknowledgeReason')
            ->getQuery()
            ->getResult();

        return $this->renderWithExtraParams(
            '@DrawSonataIntegration/Console/Execution/report.html.twig',
            [
                'action' => 'report', // to show actions buttons
                'stats' => $stats,
            ]
        );
    }
}
