<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\EmailWriter\ToUserEmailWriter;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;

class DrawUserExtensionWithEmailWritersEnabledTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        return [
            'email_writers' => true,
            'user_entity_class' => User::class,
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [ToUserEmailWriter::class];
    }
}
