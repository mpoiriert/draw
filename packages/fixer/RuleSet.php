<?php

namespace Draw\Fixer;

use Draw\Fixer\ClassNotation\ClassPrivateStaticCallFixer;
use Draw\Fixer\ClassNotation\ClassStaticCallFixer;
use PhpCsFixer\ConfigInterface;

class RuleSet
{
    public static function addCustomFixers(ConfigInterface $config): ConfigInterface
    {
        $fixers = [
            new ClassStaticCallFixer(),
            new ClassPrivateStaticCallFixer(),
        ];

        $currentFixers = $config->getCustomFixers();

        foreach ($fixers as $fixer) {
            foreach ($currentFixers as $currentFixer) {
                if ($fixer::class === $currentFixer::class) {
                    continue 2;
                }
            }
            $config->registerCustomFixers([$fixer]);
        }

        return $config;
    }

    public static function adjust(ConfigInterface $config): ConfigInterface
    {
        static::addCustomFixers($config);

        $rules = $config->getRules();

        $rules['Draw/class_static_call'] = true;
        $rules['Draw/class_private_static_call'] = true;

        $config->setRules($rules);

        return $config;
    }
}
