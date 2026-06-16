Tester
======

This package provide a set of tools to help you test your application.

## Data Tester

This library is a wrapper around **PHPUnit Assert** class to be able to use a fluent interface on the data you want to test.

Here is a quick example of how to use it in a **PHPUnit TestCase**:

```php

namespace Your\Project\Name;

use PHPUnit\Framework\TestCase;
use Draw\Component\Tester\DataTester;

class SimpleTest extends TestCase
{
    public function test()
    {
        $data = [
          'key1' => 'value1',
          'key2' => (object)['toto' => 'value']
        ];

        $dateTester = new DataTester($data);
        $dateTester
            ->assertIsArray('array')
            ->assertCount(2)
            ->path('[key1]')->assertSame('value1');
            
        $dateTester->path('[key2].toto')->assertSame('value');
    }
}
```

## PHPUnit Extension

This package also provide a PHPUnit extension to make it easier to write test.

### CarbonReset

If you are using Carbon in your project, you might want to reset the Carbon class between each test to make sure you have a consistent state.

Register the extension in your phpunit configuration file.

```xml
<phpunit bootstrap="vendor/autoload.php">
    <extensions>
        <bootstrap class="Draw\Component\Tester\PHPUnit\Extension\CarbonReset\CarbonResetExtension"/>
    </extensions>
</phpunit>
```

This will reset your carbon class between each test and test suite like it would in `TestCass::tearDown` and `TestCass::tearDownAfterClass`.

### SetUpAutowire

A bit like the Service auto wiring would work via a service container, this extension allow you to autowire properties
base on attribute that implement `AutowireInterface`.

Make sure to register is in your phpunit configuration file.

```xml
<phpunit bootstrap="vendor/autoload.php">
    <extensions>
        <bootstrap class="Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\SetUpAutowireExtension"/>
    </extensions>
</phpunit>
```

Once this is done, your test need to implement the `AutowiredInterface` interface so the extension will hook it.

```php
namespace App\Tests;

use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MyTest extends KernelTestCase implements AutowiredInterface
{
}
```

Having the extension by itself doesn't do much, you need to put some attribute on the property you need to autowire.

> Note that the autowired system doesn't work on static properties.

```php
namespace App\Tests;

use App\MyInterface;
use App\MyObject;
use App\MySecondObject;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireDouble;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase implements AutowiredInterface
{
   // Will create a stub of MyInterface and assigned it to property.
   // This can be used in conjunction with the AutowireDoubleProperty (see below).
   #[AutowireDouble]
   private MyInterface&Stub $aService
   
   // The AutowireDoubleProperty will replace the aService property of $myObject.
   #[AutowireDoubleProperty('aService')]
   private MyObject $myObject;
   
   // By defaults, it will use the same property name in the current test case, but you can specify a different one using the second parameter.
   #[AutowireDoubleProperty('aService', 'anotherProperty')]
   private MySecondObject $mySecondObject;
   
   public function setUp(): void
   {
       $this->myObject = new MyObject();
       $this->mySecondObject = new MySecondObject();
   }
}
```

> This might seem a bit useless, but in a framework context using service it will more sense.
> The `AutowireService` from [draw/tester-bundle](https://github.com/mpoiriert/tester-bundle) is a good example of this in Symfony.

Since the auto wiring is done in the `setUp` hook of phpunit extension you cannot use them in the setup method of you test.
If you need to access those property in your `setUp` method, you can use the `AutowiredCompletionAwareInterface` instead.

```php
namespace App\Tests;

use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredCompletionAwareInterface;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireDouble;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MyTest extends KernelTestCase implements AutowiredCompletionAwareInterface
{
   #[AutowireDouble]
   private MyInterface&MockObject $aService
   
    public function postAutowire(): void
    {
         $this->aService
            ->expects(static::any())
            ->method('someMethod')
            ->willReturn('someValue');
    }
}
```

#### Handling test doubles (Stubs vs. Mocks)

PHPUnit 13 introduces a strict distinction between **Stubs** and **Mocks**. If you use a `MockObject` but do not configure any
expectations on it, PHPUnit will throw a deprecation warning:
> *No expectations were configured for the mock object for App\MyInterface. Consider refactoring your test code to use a test stub
> instead. The #[AllowMockObjectsWithoutExpectations] attribute can be used to opt out of this check.*

To prevent this warning, this extension allows a property defined as a `Stub` to dynamically morph into a `MockObject` on a per-test
basis.

The extension automatically looks at the intersection type of your property to decide whether to call `createStub` or `createMock`:

```php
namespace App\Tests;

use App\MyInterface;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireDouble;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase implements AutowiredInterface
{
    // The extension detects `&Stub` and initializes a PHPUnit Stub
    #[AutowireDouble]
    private MyInterface&Stub $aService;

    // The extension detects `&MockObject` and initializes a PHPUnit MockObject
    #[AutowireDouble]
    private MyInterface&MockObject $anotherService;
}
```

For dependencies that act as a `Stub` in most tests but require expectations in a few, type-hint the property with `&Stub`.
This ensures it safely stays a lightweight Stub by default, protecting you from PHPUnit warning mentioned above. When a 
specific test method requires it to be a Mock, add the `#[AsMock]` attribute to that method. Inside the test, use native
PHP `assert` to instantly unlock full IDE autocomplete for `expects()`:

```php
namespace App\Tests;

use App\MyInterface;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AsMock;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireDouble;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase implements AutowiredInterface
{
    #[AutowireDouble]
    private MyInterface&Stub $aService;

    public function test1(): void
    {
        $this->aService
            ->method('method1')
            ->willReturn(1)
        ;

        // assertions
    }

    #[AsMock('aService')]
    public function test2(): void
    {
        \assert($this->aService instanceof MockObject);

        $this->aService
            ->expects(self::once())
            ->method('method1')
            ->willReturn(2)
        ;

        // assertions
    }
}
```

#### Creating you own Autowire attribute

You can create your own attribute to autowire your own property.

You just need to create an attribute that implement the `AutowireInterface` interface.

```php
namespace App\Test\PHPUnit\SetUpAutowire;

use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireInterface;
use PHPUnit\Framework\TestCase;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireRandomInt implements AutowireInterface
{
    // This is the priority of the autowire. The higher the number the sooner it will be called.
    // This can be important if you need to autowire a property before another one.
    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(
       private int $min = \PHP_INT_MIN, 
       private int $max = \PHP_INT_MAX
    ) {}

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        $reflectionProperty->setValue(
            $testCase,
            random_int($this->min, $this->max)
        );
    }
}
```

Now you can simply use it in your test case:

```php

namespace App\Tests;

use App\Test\PHPUnit\SetUpAutowire\AutowireRandomInt;

class MyTest extends KernelTestCase
{
    #[AutowireRandomInt(1, 10)]
    private int $randomInt;
}
```
