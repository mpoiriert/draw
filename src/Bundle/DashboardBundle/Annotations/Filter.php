<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Doctrine\Common\Annotations\Annotation\Enum;
use JMS\Serializer\Annotation as Serializer;
use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
class Filter implements VendorPropertyInterface, CanBeExcludeInterface
{
    use CanBeExcludeTrait;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var bool
     */
    private $alwaysShow = true;

    /**
     * @var null|int
     *
     * @Serializer\Exclude()
     */
    private $position;

    /**
     * @var \Draw\Bundle\DashboardBundle\Annotations\FormInput
     */
    private $input;

    /**
     * @var string|null
     *
     * @Enum({"=", "<>", ">", ">=", "<", "<=", "LIKE", "NOT LIKE", "IN", "NOT IN"})
     */
    private $comparison = '=';

    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'input' => new FormInput(),
            ],
            $values
        );

        construct($this, $values);
    }

    public function getVendorName(): string
    {
        return 'x-draw-dashboard-filter';
    }

    public function allowClassLevelConfiguration(): bool
    {
        return false;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function isAlwaysShow(): bool
    {
        return $this->alwaysShow;
    }

    public function setAlwaysShow(bool $alwaysShow): void
    {
        $this->alwaysShow = $alwaysShow;
    }

    public function getInput(): FormInput
    {
        return $this->input;
    }

    public function setInput(FormInput $input): void
    {
        $this->input = $input;
    }

    public function getComparison(): ?string
    {
        return $this->comparison;
    }

    public function setComparison(?string $comparison): void
    {
        $this->comparison = $comparison;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }
}