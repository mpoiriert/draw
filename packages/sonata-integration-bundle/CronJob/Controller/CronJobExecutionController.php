<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataIntegrationBundle\CronJob\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CronJobExecutionController extends CRUDController
{
    public function acknowledgeAction(
        CronJobExecution $execution,
        ManagerRegistry $managerRegistry,
    ): RedirectResponse {
        if (!$execution->canBeAcknowledged()) {
            $this->addFlash(
                'sonata_flash_error',
                $this->trans('cron_job_execution_cannot_be_acknowledged')
            );

            return $this->redirectToList();
        }

        $execution->acknowledge();
        $managerRegistry->getManagerForClass(CronJobExecution::class)->flush();

        $this->addFlash(
            'sonata_flash_success',
            $this->trans('cron_job_execution_successfully_acknowledged')
        );

        return $this->redirectToList();
    }
}
