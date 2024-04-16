<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataIntegrationBundle\CronJob\Controller;

use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJob;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CronJobController extends CRUDController
{
    public function queueAction(
        Request $request,
        CronJob $cronJob,
        CronJobProcessor $cronJobProcessor
    ): Response {
        $cronJobProcessor->queue($cronJob);

        $this->addFlash(
            'sonata_flash_success',
            $this->trans(
                'cron_job_successfully_queued',
                domain: 'DrawCronJobSonata'
            )
        );

        return $this->redirect(
            $this->admin->generateObjectUrl(
                'show',
                $cronJob,
                $this->getSelectedTab($request)
            )
        );
    }
}
