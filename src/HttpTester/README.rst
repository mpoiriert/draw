Php Http Tester
===============
.. image:: https://travis-ci.org/mpoiriert/php-http-tester.svg?branch=master

This library is meant to be a testing framework for http call. It is framework agnostic.
By default it does a curl call to the specified url but you can use/create a adapter for the framework you are using.

The library can be install via `Composer/Packagist <https://packagist.org/packages/draw/http-tester>`_.

In that example we are trying to have a browser flow so the usage of phpunit annotation **@depends**
and **@test** make it more readable.

Here is a quick example of how to use it in a **PHPUnit TestCase**:

.. code-block:: php

    <?php

    namespace Your\Project\Name;

    use PHPUnit\Framework\TestCase;
    use Draw\HttpTester\HttpTesterTrait;

    class SimpleTest extends TestCase
    {
        use HttpTesterTrait

        /**
         * @test
         */
        public function notAuthorizeProfileAccess()
        {
            static::$client->get('http://your.domain.com/api/me')
                ->assertStatus(403);
        }

        /**
         * @test
         * @depends notAuthorizeProfileAccess
         */
        public function login()
        {
            $testResponse = static::$client->post(
                'http://your.domain.com/api/tokens',
                json_encode([
                    'username' => 'my-username',
                    'password' => 'my-password'
                ])
            );

            $content = $testResponse
              ->assertSuccessful()
              ->assertCookie('session') // We are not debating the usage of cookie here ;)
              ->getResponseBodyContents();

            // Continue with the test of you content
            $this->assertJson($content);
        }

        /**
         * @test
         * @depends login
         */
        public function getMyProfile()
        {
            // The same client is during all test. Cookies are sent automatically between request
            $testResponse = static::$client->get('http://your.domain.com/api/me')

            $content = $testResponse
              ->assertSuccessful()
              ->getResponseBodyContents();

            // Continue with the test of you content
            $this->assertJson($content);
        }
    }

If you need to use it in another context and can still relay on PHPUnit Assertion you can simply create your the client
manually and use it:

.. code-block:: php

    <?php

    use Draw\HttpTester\Client;

    $client = new Client();

    $client->post(
        'http://your.domain.com/api/tokens',
        json_encode([
            'username' => 'my-username',
            'password' => 'my-password'
        ])
    );

By default the client will use the **Draw\HttpTester\CurlRequestExecutioner** but you can make your own by implementing
the **Draw\HttpTester\RequestExecutionerInterface**.

## Currently Supported Request Executioner

=========== ========================================================== ================
Executioner Class                                                      Package
=========== ========================================================== ================
Curl        Draw\HttpTester\CurlRequestExecutioner                     draw/http-tester
Laravel 4.2 Draw\HttpTester\Bridge\Laravel4\Laravel4RequestExecutioner draw/http-tester



** Not available yet **
There is a lot more features available, just `Read the Docs <http://php-http-tester.readthedocs.io/en/latest/>`_!