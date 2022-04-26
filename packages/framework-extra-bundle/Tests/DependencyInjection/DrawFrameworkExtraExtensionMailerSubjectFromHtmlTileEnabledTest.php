<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Mailer\EventListener\EmailSubjectFromHtmlTitleListener;

class DrawFrameworkExtraExtensionMailerSubjectFromHtmlTileEnabledTest extends DrawFrameworkExtraExtensionMailerTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['mailer']['subject_from_html_title'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.mailer.email_subject_from_html_title_listener'];
        yield [EmailSubjectFromHtmlTitleListener::class, 'draw.mailer.email_subject_from_html_title_listener'];
    }
}
