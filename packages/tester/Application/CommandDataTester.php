<?php

namespace Draw\Component\Tester\Application;

use Draw\Component\Tester\DataTester;
use Symfony\Component\Console\Command\Command;

final class CommandDataTester
{
    private null|string|array $expectedDisplay = null;
    private null|string|array $expectedErrorOutput = null;

    private int $expectedStatusCode = 0;

    /**
     * @param string|array|null $expectedDisplay     String for exact match, array to find line, null will not test the display
     * @param string|array|null $expectedErrorOutput String for exact match, array to find line, null will not test the error output
     */
    public static function create(
        int $expectedStatusCode = Command::SUCCESS,
        null|string|array $expectedDisplay = '',
        null|string|array $expectedErrorOutput = '',
    ): self {
        return (new self())->setExpectedStatusCode($expectedStatusCode)
            ->setExpectedDisplay($expectedDisplay)
            ->setExpectedErrorOutput($expectedErrorOutput);
    }

    public function getExpectedDisplay(): array|string|null
    {
        return $this->expectedDisplay;
    }

    public function setExpectedDisplay(array|string|null $expectedDisplay): self
    {
        $this->expectedDisplay = $expectedDisplay;

        return $this;
    }

    public function getExpectedErrorOutput(): array|string|null
    {
        return $this->expectedErrorOutput;
    }

    public function setExpectedErrorOutput(array|string|null $expectedErrorOutput): self
    {
        $this->expectedErrorOutput = $expectedErrorOutput;

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

    public function __invoke(DataTester $dataTester): void
    {
        $dataTester->path('statusCode')->assertEquals($this->expectedStatusCode);

        if (null !== $this->expectedErrorOutput) {
            if (\is_string($this->expectedErrorOutput)) {
                $dataTester->path('errorOutput')->assertEquals($this->expectedErrorOutput);
            } else {
                foreach ($this->expectedErrorOutput as $expectedErrorOutput) {
                    $dataTester->path('errorOutput')->assertStringContainsString($expectedErrorOutput);
                }
            }
        }

        if (null !== $this->expectedDisplay) {
            if (\is_string($this->expectedDisplay)) {
                $dataTester->path('display')->assertEquals($this->expectedDisplay);
            } else {
                foreach ($this->expectedDisplay as $expectedDisplay) {
                    $dataTester->path('display')->assertStringContainsString($expectedDisplay);
                }
            }
        }
    }
}
