Draw Tester Bundle
==================

This bundle integrate the Draw Tester Component.

To use the HttpTesterTrait in a KernelTestCase you must simply do this:

```PHP
<?php namespace App\Tests;

use Draw\Bundle\TesterBundle\Http\BrowserFactoryInterface;
use Draw\Bundle\TesterBundle\Http\HttpTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class TestCase extends KernelTestCase implements BrowserFactoryInterface
{
    use HttpTesterTrait;
    
    public function createBrowser(): AbstractBrowser
    {
       return static::bootKernel()->getContainer()->get('test.client');
    }
}
```

As you can see we are using the **HttpTesterTrait** of the **Bundle** instead of the **Component**.
This is because it as the implementation of the implementation of the **createHttpTesterClient** method.

Also you can see that we are booting a new kernel every time. It's to make sure we are using a new container
on each request like the behaviour of a normal client request will do.
