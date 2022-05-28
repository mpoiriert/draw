<?php

namespace Draw\Component\Tester\Application;

use Draw\Component\Tester\DataTester;
use Symfony\Component\Console\Command\Command;

class CommandDataTester
{
    private ?string $expectedDisplay = null;

    private array $expectedDisplayStrings = [];

    private int $expectedStatusCode = 0;

    private ?string $errorOutput = '';

    /**
     * @param string|array|null $expectedDisplay String for exact match, array to find line, null will not test the display
     */
    public static function create(int $expectedStatusCode = Command::SUCCESS, $expectedDisplay = ''): CommandDataTester
    {
        $self = (new static())->setExpectedStatusCode($expectedStatusCode);

        if (is_array($expectedDisplay)) {
            $self->setExpectedDisplayStrings($expectedDisplay);
        } else {
            $self->setExpectedDisplay($expectedDisplay);
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

    public function getExpectedDisplay(): ?string
    {
        return $this->expectedDisplay;
    }

    public function setExpectedDisplay(?string $expectedDisplay): self
    {
        $this->expectedDisplay = $expectedDisplay;

        return $this;
    }

    public function getExpectedStatusCode(): int
    {
        return $this->expectedStatusCode;
    }

    public function setExpectedStatusCode(int $expectedStatusCode): self
    {
        $this->expectedStatusCode = $expectedStatusCode;

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
        $dataTester->path('statusCode')->assertEquals($this->expectedStatusCode);

        if (null !== $this->errorOutput) {
            $dataTester->path('errorOutput')->assertEquals($this->errorOutput);
        }

        if (null !== $this->expectedDisplay) {
            $dataTester->path('display')->assertEquals($this->expectedDisplay);
        }

        foreach ($this->expectedDisplayStrings as $expectedDisplayString) {
            $dataTester->path('display')->assertStringContainsString($expectedDisplayString);
        }
    }
}
