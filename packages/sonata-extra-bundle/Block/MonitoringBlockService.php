<?php

namespace Draw\Bundle\SonataExtraBundle\Block;

use Draw\Bundle\SonataExtraBundle\Block\Event\FinalizeContextEvent;
use Draw\Bundle\SonataExtraBundle\ExpressionLanguage\ExpressionLanguage;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class MonitoringBlockService implements BlockServiceInterface
{
    use BlockTrait;

    public function __construct(
        private Environment $twig,
        private ExpressionLanguage $expressionLanguage,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'icon' => 'fas fa-line-chart',
                    'text' => 'Monitoring',
                    'translation_domain' => null,
                    'css_class' => 'bg-aqua',
                    'extra_data' => [],
                    'count' => null,
                    'link' => null,
                    'link_label' => null,
                    'template' => '@DrawSonataExtra/Block/block_monitoring.html.twig',
                    'thresholds' => [
                        'success' => [
                            'if' => 'count == 0',
                            'settings' => [
                                'css_class' => 'bg-green',
                            ],
                        ],
                        'alert' => [
                            'if' => 'count >= 1',
                            'settings' => [
                                'css_class' => 'bg-red',
                            ],
                        ],
                    ],
                ]
            );
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $this->eventDispatcher->dispatch(
            new FinalizeContextEvent($blockContext),
        );

        $count = $blockContext->getSetting('count');

        $settings = array_merge(
            $blockContext->getSettings(),
            $this->findThresholdSetting($blockContext->getSetting('thresholds'), $count)['settings'] ?? []
        );

        $response = $response ?: new Response();

        return
            $response->setContent(
                $this->twig->render(
                    $blockContext->getTemplate(),
                    [
                        'block' => $blockContext->getBlock(),
                        'settings' => $settings,
                        'count' => $count,
                        'link' => $blockContext->getSetting('link'),
                        'link_label' => $blockContext->getSetting('link_label'),
                        'translation_domain' => $blockContext->getSetting('translation_domain'),
                    ]
                )
            )
                ->setPrivate()
                ->setTtl(0);
    }

    private function findThresholdSetting(array $thresholds, int $count): array
    {
        foreach ($thresholds as $threshold) {
            if (!isset($threshold['if'])) {
                return $threshold;
            }

            if ($this->expressionLanguage->evaluate($threshold['if'], ['count' => $count])) {
                return $threshold;
            }
        }

        return [];
    }
}
