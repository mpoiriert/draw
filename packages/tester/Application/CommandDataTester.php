<?php

namespace Draw\Component\Tester\Application;

use Draw\Component\Tester\DataTester;

class CommandDataTester
{
    private ?string $display = null;

    private array $expectedDisplayStrings = [];

    private int $statusCode = 0;

    private ?string $errorOutput = '';

    public static function create(int $statusCode = 0, $display = ''): CommandDataTester
    {
        $self = (new static())->setStatusCode($statusCode);

        if (is_array($display)) {
            $self->setExpectedDisplayStrings($display);
        } else {
            $self->setDisplay($display);
        }

        return $self;
    }

    public function getExpectedDisplayStrings(): array
    {
        return $this->expectedDisplayStrings;
    }

    public function setExpectedDisplayStrings(array $strings): self
    {
        $this->expectedDisplayStrings = $strings;

        return $this;
    }

    public function getDisplay(): ?string
    {
        return $this->display;
    }

    public function setDisplay(?string $display): self
    {
        $this->display = $display;

        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getErrorOutput(): ?string
    {
        return $this->errorOutput;
    }

    public function setErrorOutput(?string $errorOutput): self
    {
        $this->errorOutput = $errorOutput;

        return $this;
    }

    public function __invoke(DataTester $dataTester)
    {
        $dataTester->path('statusCode')->assertEquals($this->statusCode);

        if (null !== $this->errorOutput) {
            $dataTester->path('errorOutput')->assertEquals($this->errorOutput);
        }

        if (null !== $this->display) {
            $dataTester->path('display')->assertEquals($this->display);
        }

        foreach ($this->expectedDisplayStrings as $expectedDisplayString) {
            $dataTester->path('display')->assertStringContainsString($expectedDisplayString);
        }
    }
}
