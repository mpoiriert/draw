<?php

namespace Draw\Component\OpenApi\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * This param convert is for auto-completion of option in IDE.
 * Private properties are converted to options.
 *
 * @Annotation
 */
class Deserialization extends ParamConverter
{
    public const DEFAULT_PARAMETER_NAME = 'target';

    /**
     * The groups use for deserialization.
     */
    private ?array $deserializationGroups;

    private ?bool $deserializationEnableMaxDepth;

    /**
     * If we must validate the deserialized object.
     */
    private ?bool $validate;

    /**
     * The validation groups to use if we do perform a validation.
     */
    private ?array $validationGroups;

    /**
     * A mapping from attribute to property path.
     */
    private array $propertiesMap;

    public function __construct(array $values)
    {
        if (!isset($values['name'])) {
            $values['name'] = self::DEFAULT_PARAMETER_NAME;
        }

        $values['converter'] = $values['converter'] ?? 'draw_open_api.request_body';

        // We set the properties in the options array since they would be override by
        // the set options if it's configuration after
        foreach ($values as $key => $value) {
            switch ($key) {
                case 'validate':
                    $values['options']['validate'] = $value;
                    unset($values[$key]);
                    break;
                case 'deserializationEnableMaxDepth':
                    $values['options']['deserializationContext']['enableMaxDepth'] = $value;
                    unset($values[$key]);
                    break;
                case 'deserializationGroups':
                    $values['options']['deserializationContext']['groups'] = $value;
                    unset($values[$key]);
                    break;
                case 'validationGroups':
                    $values['options']['validator']['groups'] = $value;
                    unset($values[$key]);
                    break;
                case 'propertiesMap':
                    $values['options']['propertiesMap'] = $value;
                    unset($values[$key]);
                    break;
            }
        }

        parent::__construct($values);
    }

    public function getPropertiesMap(): array
    {
        return $this->getOptions()['propertiesMap'] ?? [];
    }

    public function setPropertiesMap(array $propertiesMap): void
    {
        $options = $this->getOptions();
        $options['propertiesMap'] = $propertiesMap;
        $this->setOptions($options);
    }

    public function getValidate(): ?bool
    {
        return $this->getOptions()['validate'] ?? null;
    }

    public function setValidate(?bool $validate): void
    {
        $options = $this->getOptions();
        $options['validate'] = $validate;
        $this->setOptions($options);
    }

    public function getValidationGroups(): ?array
    {
        return $this->getOptions()['validator']['groups'] ?? null;
    }

    public function setValidationGroups(?array $validationGroups): void
    {
        $options = $this->getOptions();
        $options['validator']['groups'] = $validationGroups;
        $this->setOptions($options);
    }

    public function getDeserializationGroups(): ?array
    {
        return $this->getDeserializationContextOptions('groups');
    }

    public function setDeserializationGroups(?array $deserializationGroups): void
    {
        $deserializationGroups = (array) $deserializationGroups;
        $this->setDeserializationContextOptions('groups', $deserializationGroups);
    }

    public function getDeserializationEnableMaxDepth(): ?bool
    {
        return $this->getDeserializationContextOptions('enableMaxDepth');
    }

    public function setDeserializationEnableMaxDepth(?bool $deserializationEnableMaxDepth): void
    {
        $this->setDeserializationContextOptions('enableMaxDepth', $deserializationEnableMaxDepth);
    }

    private function setDeserializationContextOptions($name, $value): void
    {
        $options = $this->getOptions();
        $options['deserializationContext'][$name] = $value;
        $this->setOptions($options);
    }

    private function getDeserializationContextOptions($name)
    {
        return $this->getOptions()['deserializationContext'][$name] ?? null;
    }
}
