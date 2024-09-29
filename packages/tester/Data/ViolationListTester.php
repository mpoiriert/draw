<?php

namespace Draw\Component\Tester\Data;

use Draw\Component\Tester\DataTester;

class ViolationListTester
{
    private array $violations = [];

    public function __invoke(DataTester $tester): void
    {
        $tester->assertCount(
            \count($this->violations),
            "Current violations:\n".json_encode($tester->getData(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
        );

        foreach ($this->violations as $index => $violation) {
            foreach ($violation as $property => $value) {
                $tester->path("[{$index}].{$property}")->assertSame($value);
            }
        }
    }

    public function addViolation(string $propertyPath, string $message): static
    {
        $this->violations[] = compact('propertyPath', 'message');

        return $this;
    }

    /**
     * Check code of the last added violation.
     */
    public function code(string $code): static
    {
        $this->violations[\count($this->violations) - 1]['code'] = $code;

        return $this;
    }

    /**
     * Check invalid value on the last added violation.
     */
    public function invalidValue(mixed $invalidValue): static
    {
        $this->violations[\count($this->violations) - 1]['invalidValue'] = $invalidValue;

        return $this;
    }
}
