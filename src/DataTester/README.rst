Php Data Tester
===============
.. image:: https://travis-ci.org/mpoiriert/php-data-tester.svg?branch=master

This library is a wrapper around **PHPUnit Assert** class to be able to use a fluent interface on the data you want to test.

The library can be install via `Composer/Packagist <https://packagist.org/packages/draw/data-tester>`_.

Here is a quick example of how to use it in a **PHPUnit TestCase**:

.. code-block:: php

    <?php

    namespace Your\Project\Name;

    use PHPUnit\Framework\TestCase;
    use Draw\DataTester\Tester;

    class SimpleTest extends TestCase
    {
        public function test()
        {
            $data = [
              'key1' => 'value1',
              'key2' => (object)['toto' => 'value']
            ];

            $tester = new Tester($data);
            $tester->assertInternalType('array')
                ->assertCount(2)
                ->path('[key1]')->assertSame('value1');
            $tester->path('[key2].toto')->assertSame('value');
    }

There is a lot more features available, just `Read the Docs <http://php-data-tester.readthedocs.io/en/latest/>`_!