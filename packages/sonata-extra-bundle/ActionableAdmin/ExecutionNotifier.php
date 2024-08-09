<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Draw\Bundle\SonataExtraBundle\Notifier\Notification\SonataNotification;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExecutionNotifier implements ResetInterface
{
    private array $actionLabels = [];

    public function __construct(
        private ?NotifierInterface $notifier,
        private TranslatorInterface $translator,
        private AdminActionLoader $actionLoader,
    ) {
    }

    public function notifyExecution(ObjectActionExecutioner $objectActionExecutioner): void
    {
        if (null === $this->notifier) {
            return;
        }

        $parameters = [
            '%total%' => $objectActionExecutioner->getTotalCount(),
            '%action%' => $this->getActionLabel(
                $objectActionExecutioner->getAdmin(),
                $objectActionExecutioner->getAction()
            ),
        ];

        $count = $objectActionExecutioner->getProcessedCount();

        if ($objectActionExecutioner->isBatch() || $count > 0) {
            $subject = $this->translator
                ->trans(
                    'execution.notification.processed',
                    [
                        ...$parameters,
                        '%count%' => $count,
                    ],
                    'DrawSonataExtraBundle'
                );

            $this->notifier->send(
                (new SonataNotification($subject))
                    ->setSonataFlashType(0 === $count ? 'info' : 'success')
            );
        }

        $skipped = $objectActionExecutioner->getSkippedCount();

        $skipped = array_filter(
            $skipped,
            fn ($value) => 0 !== $value
        );

        asort($skipped);

        $skipped = array_reverse($skipped, true);

        foreach ($skipped as $reason => $count) {
            $subject = $this->translator
                ->trans(
                    'execution.notification.skipped',
                    [
                        ...$parameters,
                        '%count%' => $count,
                        '%reason%' => $this->translator->trans('execution.notification.skipped.reason.'.$reason, [], 'DrawSonataExtraBundle'),
                    ],
                    'DrawSonataExtraBundle'
                );

            $this->notifier->send(
                (new SonataNotification($subject))
                    ->setSonataFlashType('info')
            );
        }
    }

    public function notifyError(
        ObjectActionExecutioner $objectActionExecutioner,
        \Throwable $throwable,
        object $object
    ): void {
        if (null === $this->notifier) {
            return;
        }

        $admin = $objectActionExecutioner->getAdmin();

        $subject = $this->translator
            ->trans(
                'execution.notification.error',
                [
                    '%action%' => $this->getActionLabel(
                        $admin,
                        $objectActionExecutioner->getAction()
                    ),
                    '%object%' => $this->escapeHtml($admin->toString($object)),
                    '%error%' => $throwable->getMessage(),
                ],
                'DrawSonataExtraBundle'
            );

        $this->notifier->send(SonataNotification::error($subject));
    }

    private function getActionLabel(AdminInterface $admin, string $action): string
    {
        $adminCode = $admin->getCode();

        if (!isset($this->actionLabels[$adminCode][$action])) {
            $label = null;
            $adminAction = $this->actionLoader->getActions($admin)[$action] ?? null;

            if ($adminAction instanceof AdminAction) {
                $label = $this->translator
                    ->trans(
                        $adminAction->getLabel(),
                        [],
                        $adminAction->getTranslationDomain()
                    );
            }

            $label ??= $action;

            $this->actionLabels[$adminCode][$action] = $label;
        }

        return $this->actionLabels[$adminCode][$action];
    }

    private function escapeHtml(string $s): string
    {
        return htmlspecialchars($s, \ENT_QUOTES | \ENT_SUBSTITUTE);
    }

    public function reset(): void
    {
        $this->actionLabels = [];
    }
}
