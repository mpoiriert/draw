<?php

namespace Draw\Bundle\SonataExtraBundle\Extension;

use Exception;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Form\FormMapper;

class AutoHelpExtension extends AbstractAdminExtension
{
    public function configureFormFields(FormMapper $formMapper)
    {
        foreach ($formMapper->keys() as $name) {
            $fieldDescription = $formMapper->getAdmin()->getFormFieldDescription($name);
            if (!$fieldDescription instanceof BaseFieldDescription) {
                continue;
            }

            if ($fieldDescription->getHelp()) {
                continue;
            }

            try {
                $fieldDescription->setHelp($this->extractHelp($formMapper->getAdmin()->getClass(), $name));
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @param $class
     * @param $propertyName
     *
     * @return string
     */
    private function extractHelp($class, $propertyName)
    {
        $mainReflectionClass = $reflectionClass = new ReflectionClass($class);

        do {
            if ($reflectionClass->hasProperty($propertyName)) {
                $property = $reflectionClass->getProperty($propertyName);
                $docBlock = DocBlockFactory::createInstance()->create($property->getDocComment());

                return $docBlock->getSummary();
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());

        if ($mainReflectionClass->hasMethod('getTranslationEntityClass')) {
            return $this->extractHelp(
                $mainReflectionClass->getMethod('getTranslationEntityClass')->invoke(null),
                $propertyName
            );
        }

        return '';
    }
}
