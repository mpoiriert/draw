<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class FormInputAutoComplete extends FormInput
{
    public const TYPE = 'auto-complete';

    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("remoteUrl")
     */
    private $remoteUrl = null;

    /**
     * @var string|null
     *
     * @Serializer\Exclude()
     */
    private $routeName = 'drawDashboard_choices';

    /**
     * Use in conjunction of route name to generate parameters.
     *
     * @Serializer\Exclude()
     */
    private $parameters;

    public function getRemoteUrl(): ?string
    {
        return $this->remoteUrl;
    }

    public function setRemoteUrl(?string $remoteUrl): void
    {
        $this->remoteUrl = $remoteUrl;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(?string $routeName): void
    {
        $this->routeName = $routeName;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters($parameters): void
    {
        if ($parameters instanceof ParametersInterface) {
            $parameters = $parameters->toArray();
        }
        $this->parameters = $parameters;
    }
}
