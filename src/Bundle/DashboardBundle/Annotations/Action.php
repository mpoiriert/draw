<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Bundle\DashboardBundle\Annotations\Button\Button;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\VendorInterface;
use JMS\Serializer\Annotation as Serializer;
use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
class Action implements VendorInterface
{
    const TYPE = 'generic';

    /**
     * @var string|null
     */
    private $type = self::TYPE;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var \Draw\Bundle\DashboardBundle\Annotations\Button\Button|null
     */
    private $button;

    /**
     * @var \Draw\Bundle\DashboardBundle\Annotations\Flow|null
     */
    private $flow;

    /**
     * @var boolean
     *
     * @Serializer\Exclude()
     */
    private $accessDenied = false;

    /**
     * @var string|null
     *
     * @Serializer\Exclude()
     */
    private $routeName;

    /**
     * @var string|null
     */
    private $href = null;

    /**
     * @var string|null
     *
     * @Serializer\Exclude()
     */
    private $path = null;

    /**
     * @var string|null
     */
    private $method = null;

    /**
     * @var Operation|null
     *
     * @Serializer\Exclude()
     */
    private $operation;

    /**
     * The class that are a target of this action. Only use by the backend.
     *
     * @var string[]|null
     *
     * @Serializer\Exclude()
     */
    private $targets;

    /**
     * @var bool|null
     *
     * @Serializer\Exclude()
     */
    private $isInstanceTarget;

    /**
     * @var string
     *
     * @Serializer\Exclude()
     */
    private $requestAttribute = 'object';

    /**
     * @var array
     *
     * @Serializer\Exclude()
     */
    private $templates = [];

    private $title;

    public function __construct(array $values = [])
    {
        $values['type'] = static::TYPE;
        $values = array_merge(
            [
                'name' => $values['type']
            ],
            $values
        );

        construct($this, $values);
    }

    public function getVendorName(): string
    {
        return 'x-draw-dashboard-action';
    }

    public function allowClassLevelConfiguration(): bool
    {
        return false;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getButton(): ?Button
    {
        return $this->button;
    }

    public function setButton(?Button $button): void
    {
        $this->button = $button;
    }

    public function getFlow(): ?Flow
    {
        return $this->flow;
    }

    public function setFlow(?Flow $flow): void
    {
        $this->flow = $flow;
    }

    public function getAccessDenied(): bool
    {
        return $this->accessDenied;
    }

    public function setAccessDenied(bool $accessDenied): void
    {
        $this->accessDenied = $accessDenied;
    }

    public function getHref(): ?string
    {
        return $this->href;
    }

    public function setHref(?string $href): void
    {
        $this->href = $href;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): void
    {
        $this->method = ($method ? strtoupper($method) : null);
    }

    public function getTargets(): ?array
    {
        return $this->targets;
    }

    public function setTargets(?array $targets): void
    {
        $this->targets = $targets;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(?string $routeName): void
    {
        $this->routeName = $routeName;
    }

    public function getOperation(): ?Operation
    {
        return $this->operation;
    }

    public function setOperation(?Operation $operation): void
    {
        $this->operation = $operation;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getRequestAttribute(): string
    {
        return $this->requestAttribute;
    }

    public function setRequestAttribute(string $requestAttribute): void
    {
        $this->requestAttribute = $requestAttribute;
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function setTemplates(array $templates): void
    {
        $this->templates = $templates;
    }

    public function getTemplate($name, $default = null)
    {
        return $this->templates[$name] ?? $default;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = Translatable::set($this->title, $title);
    }

    public function getIsInstanceTarget(): ?bool
    {
        return $this->isInstanceTarget;
    }

    public function setIsInstanceTarget(?bool $isInstanceTarget): void
    {
        $this->isInstanceTarget = $isInstanceTarget;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}