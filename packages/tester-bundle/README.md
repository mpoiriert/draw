Draw Tester Bundle
==================

This bundle integrate the Draw Tester Component.

It also provides test helpers to make it easier to test your Symfony application.

## Kernel Testing

When configuring your kernel you may want to test that everything is hooked up correctly.

There is the list of service, event dispatcher, command etc.

There is some TestCase/Trait to help you do that (work in progress).

### Event Dispatcher

Relying on the `debug:event-dispatcher` command we can dump the list of event listeners and validated it against the expected list.

```php
<?php

namespace App\Tests;

use Draw\Bundle\TesterBundle\EventDispatcher\EventDispatcherTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AppKernelTest extends KernelTestCase
{
    use EventDispatcherTesterTrait;

    public function testEventDispatcherConfiguration(): void
    {
        $this->assertEventDispatcherConfiguration(
            __DIR__.'/fixtures/AppKernelTest/testEventDispatcherConfiguration/event_dispatcher.xml',
           'event_dispatcher' // This is the default value, same as the debug:event-dispatcher command
        );
    }
}
```

The first time you run this test it will fail and dump the current configuration in the `event_dispatcher.xml` file.

Commit this file, next time your rune this test you will be able to validate that the configuration is still valid.

If you change the listener in your code or change your dependencies you can run the test again and see the diff.

This will allow you to see if some external listeners changed at the same time.

## PHPUnit Extension

This bundle also provide a PHPUnit extension to make it easier to test your Symfony application.

### KernelShutdown

Sometimes you need to use the kernel/container in a tearDownAfterClass method.

```php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MyTest extends KernelTestCase
{
    use KernelShutdownTrait;

    public static function tearDownAfterClass(): void
    {
        static::getContainer()->get('my_service')->doSomething();
    }
}
```

Since symfony shutdown the kernel in the tearDown method, this will boot a new kernel cause a kernel to be up.

Adding the KernelShutdownExtension will make sure the kernel is shutdown after the test.

```xml
<phpunit bootstrap="vendor/autoload.php">
    <extensions>
        <!-- It must be after any extension that could also boot a kernel -->
        <bootstrap class="Draw\Bundle\TesterBundle\PHPUnit\Extension\KernelShutdown\KernelShutdownExtension"/>
    </extensions>
</phpunit>
```

### SetUpAutowire addon

The [draw/tester](https://github.com/mpoiriert/tester) component provide a way to autowire property in your test.

This bundle provide some custom Autowire attribute that can use in the context of a Symfony test cases.

Make sure to register is in your phpunit configuration file. as explained in the `draw/tester` documentation.

```xml
<phpunit bootstrap="vendor/autoload.php">
    <extensions>
        <bootstrap class="Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\SetUpAutowireExtension"/>
    </extensions>
</phpunit>
```

Here is an example of attribute you can use in your test case:

```php
namespace App\Tests;

use App\AServiceInterface;
use App\MyService;
use Draw\Bundle\TesterBundle\Messenger\TransportTester;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireParameter;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireTransportTester;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireMock;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MyTest extends KernelTestCase implements AutowiredInterface
{
   // From d
   #[AutowireMock]
   private AServiceInterface&MockObject $aService
   
   // Will hook MyService from the test container. Your test need to extend KernelTestCase.
   //
   // The AutowireMockProperty will replace the aService property of $myService. 
   // By defaults, it will use the same property name in the current test case but you can specify a different one using the second parameter.
   #[AutowireService]
   #[AutowireMockProperty('aService')]
   private MyService $myService;
   
   // Will hook the parameter from the container using ParameterBagInterface::resolveValue
   #[AutowireParameter('%my_parameter%')]
   private string $parameter;
   
   // Will hook the transport tester from the container.
   #[AutowireTransportTester('async')]
   private TransportTester $transportTester;
}
```

If you extend from a `WebTestCase` you can also use the `AutowireClient` attribute to get a client.

By using the `AutowireClient` in conjunction with the `AutowireService` you are use that the client is
created before the other service preventing the exception:

`Booting the kernel before calling "Symfony\Bundle\FrameworkBundle\Test\WebTestCase::createClient" is not supported, the kernel should only be booted once`

```php
namespace App\Tests;

use App\MyService;use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;use Symfony\Bundle\FrameworkBundle\KernelBrowser;use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MyTest extends WebTestCase implements AutowiredInterface
{
    #[AutowireClient]
    private KernelBrowser $client;
   
    public function testSomething(): void
    {
        $this->client->request('GET', '/my-route');
        
        static::assertResponseIsSuccessful();
    }
}
```

This is the same client as the one you get from the `WebTestCase`, you can use it the same way.

Note that the `AutowireClient` attribute have an `options` and `server` parameters like you would do when calling the `createClient` method.
