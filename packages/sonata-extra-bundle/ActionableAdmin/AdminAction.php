<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\String\UnicodeString;

#[Exclude]
class AdminAction
{
    private bool $forEntityListAction;

    private array $forActions = [
        '_default' => true,
    ];

    private ?string $controller = null;

    private ?string $batchController = null;

    private ?string $icon = null;

    private string $urlSuffix;

    private string $access;

    private string|false|null $label;
    private string|false|null $translationDomain = null;

    public function __construct(
        private string $name,
        private bool $targetEntity,
    ) {
        $this->forEntityListAction = $this->targetEntity;
        $this->label = $this->name;
        $this->access = (new UnicodeString($this->name))
            ->snake()
            ->upper()
            ->toString();

        $this->urlSuffix = (new UnicodeString($this->name))
            ->snake()
            ->replace('_', '-')
            ->toString();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTargetEntity(): bool
    {
        return $this->targetEntity;
    }

    public function getAccess(): string
    {
        return $this->access;
    }

    public function setAccess(string $access): self
    {
        $this->access = $access;

        return $this;
    }

    public function getUrlSuffix(): string
    {
        return $this->urlSuffix;
    }

    public function setUrlSuffix(string $urlSuffix): self
    {
        $this->urlSuffix = $urlSuffix;

        return $this;
    }

    public function getLabel(): bool|string|null
    {
        return $this->label;
    }

    public function setLabel(bool|string|null $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getTranslationDomain(): bool|string|null
    {
        return $this->translationDomain;
    }

    public function setTranslationDomain(bool|string|null $translationDomain): self
    {
        $this->translationDomain = $translationDomain;

        return $this;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    public function getBatchController(): ?string
    {
        return $this->batchController;
    }

    public function setBatchController(?string $batchController): self
    {
        $this->batchController = $batchController;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getForEntityListAction(): bool
    {
        return $this->forEntityListAction;
    }

    public function setForEntityListAction(bool $forEntityListAction): self
    {
        $this->forEntityListAction = $forEntityListAction;

        return $this;
    }

    public function allowForAllActions(): self
    {
        $this->forActions = [
            '_default' => true,
        ];

        return $this;
    }

    public function clearForActions(): self
    {
        $this->forActions = [
            '_default' => false,
        ];

        return $this;
    }

    public function removeForActions(string ...$actions): self
    {
        foreach ($actions as $action) {
            $this->forActions[$action] = false;
        }

        return $this;
    }

    public function addForActions(string ...$actions): self
    {
        foreach ($actions as $action) {
            $this->forActions[$action] = true;
        }

        return $this;
    }

    public function getForActions(): array
    {
        return $this->forActions;
    }

    public function isForAction(string $action): bool
    {
        return $this->forActions[$action] ?? $this->forActions['_default'];
    }
}
