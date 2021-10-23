<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Controller;

use Draw\Bundle\OpenApiBundle\Tests\TestCase;
use Draw\Component\Tester\Data\AgainstJsonFileTester;

class OpenApiControllerTest extends TestCase
{
    private $writeFile = false;

    public function testApiDocAction()
    {
        $this->httpTester()
            ->get('/api-doc')
            ->assertStatus(302)
            ->assertHeader('Location', '/bundles/drawopenapi/sandbox/index.html?url=http://localhost/api-doc.json');
    }

    public function testApiDocActionJson()
    {
        $file = __DIR__.'/fixtures/OpenApiControllerTest_testApiDocAction_json.json';

        $responseTester = $this->httpTester()
            ->get('/api-doc.json')
            ->assertStatus(200);

        $jsonTester = $responseTester
            ->toJsonDataTester();

        // We keep this since the file must be rewrite often
        if ($this->writeFile) {
            $content = $responseTester->getResponseBodyContents();
            file_put_contents($file, json_encode(json_decode($content), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        $jsonTester
            ->test(new AgainstJsonFileTester($file));
    }

    public function testWriteFile(): void
    {
        $this->assertFalse($this->writeFile, 'Write file true should not be committed.');
    }
}
