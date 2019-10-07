UPGRADE TO 0.5
==============

The version 0.5 aim to a pre-release of the version 1.0, so things will change less here.

The general draw libraries will try to keep up as quickly as possible with Symfony framework.
We are starting with version 4.3 since it's the stable latest stable release of symfony.

We are also dropping support of PHP < 7.2 since the end support for 7.1 is almost over.

Support to other framework have been dropped. Other independent repository might be created eventually if needed.

There is a lot of change into the existing component and some other project have been integrated 
to this main repository.

Here are the change to guide you trough your upgrade.

 - The folder tree map the structure of Symfony: Component, Bridge, Bundle
 - Draw\DataTester and Draw\HttpTester have been merge under Draw\Component\Tester (see more under class mapping)


## Class Mapping

Here is a mapping of old class name to new class name.

 - Draw\DateTester\Tester -> Draw\Component\Tester\DataTester
 - Draw\DataTester\AgainstJsonFileTester -> Draw\Component\Tester\Data\AgainstJsonFileTester
 - Draw\HttpTester\* -> Draw\Component\Tester\Http
 - Draw\HttpTester\BridgeClientFactory -> **Removed**
 - Draw\HttpTester\ClientFactoryInterface -> **Removed**
 
## Draw\Component\Tester\Http\HttpTesterTrait

Instead of using the static $client attribute you must now use the instance method **httpTester**

```PHP

class MyTest
{
    use Draw\Component\Tester\Http\HttpTesterTrait;
    
    public function test()
    {
      // Before 
      // static::$client->get('/api/users/1');
      
      // Now
      $this->httpTester()
        ->get('/api/users/1');
    }
}

```