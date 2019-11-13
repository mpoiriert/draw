<?php namespace Draw\Component\Tester\Data;

use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\Assert;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AgainstJsonFileTester
{
    private $fullJsonFilePath;

    private $propertyPathsCheck;

    public function __construct($fullJsonFilePath, $propertyPathsCheck = [])
    {
        $this->fullJsonFilePath = $fullJsonFilePath;
        $this->propertyPathsCheck = $propertyPathsCheck;
    }

    public function __invoke(DataTester $tester)
    {
        if(!file_exists($this->fullJsonFilePath)) {
            Assert::fail(
                "Fail path does not exists to validate data.\nFile path: " . $this->fullJsonFilePath . "\nData:\n" .
                json_encode($tester->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        $data = json_decode(file_get_contents($this->fullJsonFilePath));

        if($this->propertyPathsCheck) {
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach($this->propertyPathsCheck as $path => $callable) {
                if(!is_callable($callable)) {
                    $value = $callable;
                    $callable = function(DataTester $tester) use ($value, $path) {
                        $tester->assertEquals($value, 'Path: ' . $path);
                    };
                }

                $pathTester = $tester->path($path);
                $pathTester->test($callable);
                $newValue = $pathTester->getData();
                $accessor->setValue($data, $path, $newValue);
            }
        }

        $tester->assertEquals($data);
    }
}