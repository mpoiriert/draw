<?php

namespace Draw\Component\Tester\Data;

use Draw\Component\Tester\DataTester;

class ViolationListTester
{
    private $violations;

    public function __invoke(DataTester $tester)
    {
        $tester->assertCount(
            count($this->violations),
            "Current violations:\n".json_encode($tester->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        foreach ($this->violations as $index => $violation) {
            foreach ($violation as $property => $value) {
                $tester->path("[$index].$property")->assertSame($value);
            }
        }
    }

    /**
     * @param $propertyPath
     * @param $message
     *
     * @return $this
     */
    public function addViolation($propertyPath, $message)
    {
        $this->violations[] = compact('propertyPath', 'message');

        return $this;
    }

    /**
     * Check code of the last added violation.
     *
     * @param $code
     *
     * @return $this
     */
    public function code($code)
    {
        $this->violations[count($this->violations) - 1]['code'] = $code;

        return $this;
    }

    /**
     * Check invalid value on the last added violation.
     *
     * @param $invalidValue
     *
     * @return $this
     */
    public function invalidValue($invalidValue)
    {
        $this->violations[count($this->violations) - 1]['invalidValue'] = $invalidValue;

        return $this;
    }
}
