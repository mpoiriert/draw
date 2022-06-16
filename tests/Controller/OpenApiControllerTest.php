<?php

namespace App\Tests\Controller;

use App\Tests\TestCase;
use Draw\Component\Tester\Data\AgainstJsonFileTester;

class OpenApiControllerTest extends TestCase
{
    private bool $writeFile = false;

    public function testApiDoc(): void
    {
        $file = __DIR__.'/fixtures/api-doc.json';

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
        static::assertFalse($this->writeFile, 'Write file true should not be committed.');
    }
}
