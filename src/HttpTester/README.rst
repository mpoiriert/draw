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

There is a lot more features available, just `Read the Docs <http://php-http-tester.readthedocs.io/en/latest/>`_!