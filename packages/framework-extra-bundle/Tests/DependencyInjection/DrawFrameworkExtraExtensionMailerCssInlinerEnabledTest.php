<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Mailer\EventListener\EmailCssInlinerListener;

class DrawFrameworkExtraExtensionMailerCssInlinerEnabledTest extends DrawFrameworkExtraExtensionMailerTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['mailer']['css_inliner'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.mailer.email_css_inline_listener'];
        yield [EmailCssInlinerListener::class, 'draw.mailer.email_css_inline_listener'];
    }
}
