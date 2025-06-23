<?php

namespace Draw\Component\Tester\Data;

use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AgainstJsonFileTester
{
    public function __construct(private string $fullJsonFilePath, private array $propertyPathsCheck = [])
    {
    }

    public function __invoke(DataTester $tester): void
    {
        // file_put_contents($this->fullJsonFilePath, json_encode($tester->getData(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR));

        if (!file_exists($this->fullJsonFilePath)) {
            Assert::fail(
                "Fail path does not exists to validate data.\nFile path: ".$this->fullJsonFilePath."\nData:\n".
                json_encode($tester->getData(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
            );
        }

        $data = json_decode(file_get_contents($this->fullJsonFilePath), null, 512, \JSON_THROW_ON_ERROR);

        if ($this->propertyPathsCheck) {
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($this->propertyPathsCheck as $path => $callable) {
                if ($callable instanceof Constraint) {
                    $constraint = $callable;
                    $callable = static function (DataTester $dataTester) use ($constraint, $path): void {
                        TestCase::assertThat($dataTester->getData(), $constraint, 'Path: '.$path);
                    };
                }

                if (!\is_callable($callable)) {
                    $value = $callable;
                    $callable = static function (DataTester $tester) use ($value, $path): void {
                        $tester->assertEquals($value, 'Path: '.$path);
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
