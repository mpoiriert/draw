<?php namespace Draw\Bundle\OpenApiBundle\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * This param convert is for auto-completion of option in ide.
 * Private properties are converted to options
 *
 * @Annotation
 */
class Deserialization extends ParamConverter
{
    /**
     * The groups use for deserialization
     *
     * @var array
     */
    private $deserializationGroups;

    /**
     * @var boolean
     */
    private $deserializationEnableMaxDepth;

    /**
     * If we must validate the deserialized object
     *
     * @var boolean
     */
    private $validate;

    /**
     * The validation groups to use if we do perform a validation
     *
     * @var array
     */
    private $validationGroups;

    /**
     * A mapping from attribute to property path
     *
     * @var array
     */
    private $propertiesMap = [];

    public function __construct(array $values)
    {
        $values['converter'] = $values['converter'] ?? 'draw_open_api.request_body';

        // We set the properties in the options array since they would be overriden by
        // the set options if it's configuration after
        foreach($values as $key => $value) {
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

    public function setPropertiesMap(array $propertiesMap)
    {
        $options = $this->getOptions();
        $options['propertiesMap'] = $propertiesMap;
        $this->setOptions($options);
    }

    /**
     * @return bool
     */
    public function getValidate()
    {
        return $this->getOptions()['validate'] ?? null;
    }

    /**
     * @param bool $validate
     */
    public function setValidate($validate)
    {
        $options = $this->getOptions();
        $options['validate'] = $validate;
        $this->setOptions($options);
    }

    /**
     * @return array
     */
    public function getValidationGroups()
    {
        $options = $this->getOptions();
        return $options['validator']['groups'] ?? null;
    }

    /**
     * @param array $validationGroups
     */
    public function setValidationGroups($validationGroups)
    {
        $options = $this->getOptions();
        $options['validator']['groups'] = $validationGroups;
        $this->setOptions($options);
    }

    /**
     * @return array
     */
    public function getDeserializationGroups()
    {
        return $this->getDeserializationContextOptions('groups', null);
    }

    /**
     * @param array $deserializationGroups
     */
    public function setDeserializationGroups($deserializationGroups)
    {
        $deserializationGroups = (array)$deserializationGroups;
        $this->setDeserializationContextOptions('groups', $deserializationGroups);
    }

    /**
     * @return bool
     */
    public function getDeserializationEnableMaxDepth()
    {
        return $this->getDeserializationContextOptions('enableMaxDepth', null);
    }

    /**
     * @param bool $deserializationEnableMaxDepth
     */
    public function setDeserializationEnableMaxDepth($deserializationEnableMaxDepth)
    {
        $this->setDeserializationContextOptions('enableMaxDept', $deserializationEnableMaxDepth);
    }

    private function setDeserializationContextOptions($name, $value)
    {
        $options = $this->getOptions();
        $options['deserializationContext'][$name] = $value;
        $this->setOptions($options);
    }

    private function getDeserializationContextOptions($name, $default = null)
    {
        $options = $this->getOptions();
        return $options['deserializationContext'][$name] ?? $default;
    }
}