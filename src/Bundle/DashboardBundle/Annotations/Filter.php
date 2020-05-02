<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * @Annotation
 */
class Filter extends BaseAnnotation implements VendorPropertyInterface
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var bool
     */
    private $alwaysShow = true;

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
        if(!isset($values['input'])) {
            $values['input'] = new FormInput();
        }
        parent::__construct($values);
    }

    public function getVendorName(): string
    {
        return 'x-draw-dashboard-filter';
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
}