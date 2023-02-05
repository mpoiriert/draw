<?php

namespace App\Tests\Controller;

use Draw\Bundle\TesterBundle\WebTestCase;

class OpenApiControllerTest extends WebTestCase
{
    private bool $writeFile = false;

    public function testApiDoc(): void
    {
        $file = __DIR__.'/fixtures/api-doc.json';

        $client = static::createJsonClient();

        $client->request('get', '/api-doc.json');

        static::assertResponseIsSuccessful();
        static::assertResponseIsJson();

        // We keep this since the file must be rewritten often
        if ($this->writeFile) {
            $content = static::getResponseContent();
            file_put_contents(
                $file,
                json_encode(
                    json_decode($content, null, 512, \JSON_THROW_ON_ERROR),
                    \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES
                )
            );
        }

        static::assertResponseJsonAgainstFile($file);
    }

    public function testWriteFile(): void
    {
        static::assertFalse($this->writeFile, 'Write file true should not be committed.');
    }
}
