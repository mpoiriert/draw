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