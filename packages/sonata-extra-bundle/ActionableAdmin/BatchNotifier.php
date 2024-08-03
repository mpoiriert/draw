<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Draw\Bundle\SonataExtraBundle\Notifier\Notification\SonataNotification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BatchNotifier
{
    public function __construct(
        private ?NotifierInterface $notifier,
        private TranslatorInterface $translator
    ) {
    }

    public function notifyBatch(BatchIterator $batchIterator): void
    {
        if (null === $this->notifier) {
            return;
        }

        $count = $batchIterator->getProcessedCount();

        $subject = $this->translator
            ->trans(
                'batch.notification.processed',
                [
                    '%count%' => $count,
                ],
                'DrawSonataExtraBundle'
            );

        $this->notifier->send(
            (new SonataNotification($subject))
                ->setSonataFlashType(0 === $count ? 'info' : 'success')
        );

        $skipped = $batchIterator->getSkippedCount();

        $skipped = array_filter(
            $skipped,
            fn ($value) => 0 !== $value
        );

        asort($skipped);

        $skipped = array_reverse($skipped, true);

        foreach ($skipped as $reason => $count) {
            $subject = $this->translator
                ->trans(
                    'batch.notification.skipped',
                    [
                        '%count%' => $count,
                        '%reason%' => $this->translator->trans('batch.notification.skipped.reason.'.$reason, [], 'DrawSonataExtraBundle'),
                    ],
                    'DrawSonataExtraBundle'
                );

            $this->notifier->send(
                (new SonataNotification($subject))
                    ->setSonataFlashType('info')
            );
        }
    }
}
