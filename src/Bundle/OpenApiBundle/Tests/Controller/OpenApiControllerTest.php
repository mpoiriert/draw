<?php namespace Draw\Bundle\OpenApiBundle\Tests\Controller;

use Draw\Bundle\OpenApiBundle\Tests\TestCase;
use Draw\Component\Tester\Data\AgainstJsonFileTester;

class OpenApiControllerTest extends TestCase
{
    public function testApiDocAction()
    {
        $this->httpTester()
            ->get('/api-doc')
            ->assertStatus(302)
            ->assertHeader('Location', 'http://petstore.swagger.io/?url=/api-doc.json');
    }

    public function testApiDocAction_json()
    {
        $this->httpTester()
            ->get('/api-doc.json')
            ->assertStatus(200)
            ->toJsonDataTester()
            ->test(
                new AgainstJsonFileTester(__DIR__ . '/fixtures/OpenApiControllerTest_testApiDocAction_json.json')
            );
    }
}