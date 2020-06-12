<?php

namespace Draw\Bundle\TesterBundle\PhpUnit;

use Draw\Bundle\TesterBundle\DrawTesterBundle;
use Draw\Component\Tester\Container\ServiceTestInterface;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;

class ServiceTestsListener implements TestListener
{
    use TestListenerDefaultImplementation;

    private $extracted = [];

    public function startTestSuite(TestSuite $suite): void
    {
        $this->parseTest($suite);
    }

    public function startTest(Test $test): void
    {
        $this->parseTest($test);
    }

    private function parseTest(Test $test)
    {
        $hash = spl_object_hash($test);
        if (isset($this->extracted[$hash])) {
            return;
        }

        $this->extracted[$hash] = true;

        if ($test instanceof ServiceTestInterface) {
            DrawTesterBundle::addServicesToTest(call_user_func([get_class($test), 'getServiceIdsToTest']));
        }

        if ($test instanceof TestSuite) {
            $this->parseTest($test);
        }
    }
}
