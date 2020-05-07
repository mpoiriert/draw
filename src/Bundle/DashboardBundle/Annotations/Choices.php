<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class Choices extends BaseAnnotation
{
    /**
     * @var array
     */
    private $choices;

    /**
     * @var string|null
     */
    private $translationDomain;

    private $assoc = false;

    /**
     * Default value if not set is choices
     *
     * @param $value
     */
    public function setValue($value)
    {
        $this->setChoices($value);
    }

    public function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }

    public function setTranslationDomain(?string $translationDomain): void
    {
        $this->translationDomain = $translationDomain;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function getAssoc(): bool
    {
        return $this->assoc;
    }

    public function setAssoc(bool $assoc): void
    {
        $this->assoc = $assoc;
    }

    public function setChoices(array $choices): void
    {
        $this->choices = $choices;
    }

    public function toArray(): array
    {
        $choices = $this->choices;
        if(is_array($choices) && count($choices)) {
            if(!is_numeric(array_keys($choices)[0])) {
                $newChoices = [];
                foreach($choices as $label => $value) {
                    $newChoices[] = [
                        'label' => $translatable = new Translatable($label),
                        'value' => $value
                    ];
                    $translatable->setDomain($this->translationDomain);
                }
                $choices = $newChoices;
            } elseif(!is_array($choices[0])) {
                $newChoices = [];
                foreach($choices as $value) {
                    $newChoices[] = [
                        'label' => $translatable = new Translatable($value),
                        'value' => $value
                    ];
                    $translatable->setDomain($this->translationDomain);
                }
                $choices = $newChoices;
            }
        }

        if($this->assoc) {
            $newChoices = [];
            foreach($choices as $choice) {
                $newChoices[$choice['value']] = $choice['label'];
            }
            $choices = $newChoices;
        }

        return $choices;
    }
}