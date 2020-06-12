<?php

namespace Draw\Component\Tester\Application;

use Draw\Component\Tester\DataTester;

class CommandDataTester
{
    private $display = '';

    private $statusCode = 0;

    private $errorOutput = '';

    public static function create(int $statusCode = 0, string $display = ''): CommandDataTester
    {
        return (new static())->setStatusCode($statusCode)->setDisplay($display);
    }

    /**
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * @return CommandDataTester
     */
    public function setDisplay(string $display)
    {
        $this->display = $display;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return CommandDataTester
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }

    /**
     * @return CommandDataTester
     */
    public function setErrorOutput(string $errorOutput)
    {
        $this->errorOutput = $errorOutput;

        return $this;
    }

    public function __invoke(DataTester $dataTester)
    {
        $dataTester->path('statusCode')->assertEquals($this->statusCode);
        $dataTester->path('display')->assertEquals($this->display);
        $dataTester->path('errorOutput')->assertEquals($this->errorOutput);
    }
}
