<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireConfigurableInterface;
use PHPUnit\Runner\Extension\ParameterCollection;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
#[Exclude]
class AutowireAdminUserEntity extends AutowireEntity implements AutowireConfigurableInterface
{
    public function configure(ParameterCollection $parameterCollection): void
    {
        if (!$parameterCollection->has('DrawAutowireAdminUserEntityJsonCriteria')) {
            throw new \RuntimeException('DrawAutowireAdminUserEntityJsonCriteria parameter is required.');
        }

        $criteria = $parameterCollection->get('DrawAutowireAdminUserEntityJsonCriteria');

        $this->setCriteria(json_decode($criteria, true, 512, \JSON_THROW_ON_ERROR));
    }
}
