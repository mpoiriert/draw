<?php

namespace Draw\Component\Tester\Tests\Http\Request;

use Draw\Component\Tester\Http\Request\BodyParser;
use PHPUnit\Framework\TestCase;

class BodyParserTest extends TestCase
{
    /**
     * @var BodyParser
     */
    private $bodyParser;

    private $boundary = 'V2ymHFg03ehbqgZCaKO6jy';

    protected function setUp(): void
    {
        $this->bodyParser = new BodyParser();
    }

    public function testParseFormDataOneField()
    {
        $contentType = "Content-Type: multipart/mixed; boundary={$this->boundary}";
        $body = <<<BODY
------------{$this->boundary}
Content-Disposition: form-data; name="field"

value
------------{$this->boundary}
BODY;

        static::assertSame(
            [
                'post' => [
                    'field' => 'value',
                ],
                'files' => [],
            ],
            $this->bodyParser->parse($body, $contentType)
        );
    }

    public function testParseFormDataTwoFields()
    {
        $contentType = "Content-Type: multipart/mixed; boundary={$this->boundary}";
        $body = <<<BODY
------------{$this->boundary}
Content-Disposition: form-data; name="field"

value
------------{$this->boundary}
Content-Disposition: form-data; name="field2"

value2
------------{$this->boundary}
BODY;

        static::assertSame(
            [
                'post' => [
                    'field' => 'value',
                    'field2' => 'value2',
                ],
                'files' => [],
            ],
            $this->bodyParser->parse($body, $contentType)
        );
    }

    public function testParseFormDataFieldArray()
    {
        $contentType = "Content-Type: multipart/mixed; boundary={$this->boundary}";
        $body = <<<BODY
------------{$this->boundary}
Content-Disposition: form-data; name="field[]"

value
------------{$this->boundary}
BODY;

        static::assertSame(
            [
                'post' => [
                    'field' => ['value'],
                ],
                'files' => [],
            ],
            $this->bodyParser->parse($body, $contentType)
        );
    }

    public function testParseFormDataFieldContentWithAmpersand()
    {
        $contentType = "Content-Type: multipart/mixed; boundary={$this->boundary}";
        $body = <<<BODY
------------{$this->boundary}
Content-Disposition: form-data; name="field"

value&
------------{$this->boundary}
BODY;

        static::assertSame(
            [
                'post' => [
                    'field' => 'value&',
                ],
                'files' => [],
            ],
            $this->bodyParser->parse($body, $contentType)
        );
    }

    public function testParseFormDataFieldContentWithEqual()
    {
        $contentType = "Content-Type: multipart/mixed; boundary={$this->boundary}";
        $body = <<<BODY
------------{$this->boundary}
Content-Disposition: form-data; name="field"

value=
------------{$this->boundary}
BODY;

        static::assertSame(
            [
                'post' => [
                    'field' => 'value=',
                ],
                'files' => [],
            ],
            $this->bodyParser->parse($body, $contentType)
        );
    }

    public function testParseFormDataFile()
    {
        $contentType = "Content-Type: multipart/mixed; boundary={$this->boundary}";
        $body = <<<BODY
------------{$this->boundary}
Content-Disposition: form-data; name="file"; filename="filename.txt"
Content-Type: text/plain

content of file
------------{$this->boundary}
BODY;

        $result = $this->bodyParser->parse($body, $contentType);

        static::assertTrue(isset($result['files']['file']['tmp_name']));

        $tmp_name = $result['files']['file']['tmp_name'];

        static::assertFileIsReadable($tmp_name);

        static::assertSame(
            'content of file',
            file_get_contents($tmp_name)
        );

        static::assertSame(
            [
                'post' => [],
                'files' => [
                    'file' => [
                        'name' => 'filename.txt',
                        'type' => 'text/plain',
                        'tmp_name' => $tmp_name,
                        'error' => \UPLOAD_ERR_OK,
                        'size' => 15,
                    ],
                ],
            ],
            $result
        );
    }

    /**
     * Test base on a complex example found in comment of PHP doc.
     *
     * @see http://php.net/manual/en/features.file-upload.post-method.php#121833
     */
    public function testParseFormDataFileNested()
    {
        $contentType = "Content-Type: multipart/mixed; boundary={$this->boundary}";

        $body = '';

        $names = [
            'nested[]' => 'nested_one.txt',
            'nested[root]' => 'nested_root.txt',
            'nested[][]' => 'nested_two.txt',
            'nested[][parent]' => 'nested_parent.txt',
            'nested[][][]' => 'nested_three.txt',
            'nested[][][child]' => 'nested_child.txt',
        ];

        foreach ($names as $formName => $fileName) {
            $body .= <<<BODY
------------{$this->boundary}
Content-Disposition: form-data; name="{$formName}"; filename="{$fileName}"
Content-Type: text/plain

content of file
BODY;
        }

        $body .= "------------{$this->boundary}";

        $result = $this->bodyParser->parse($body, $contentType);

        static::assertTrue(isset($result['files']['nested']['name']));

        static::assertSame(
            $result['files']['nested']['name'],
            [
                'nested_one.txt',
                'root' => 'nested_root.txt',
                [
                    'nested_two.txt',
                ],
                [
                    'parent' => 'nested_parent.txt',
                ],
                [
                    [
                        'nested_three.txt',
                    ],
                ],
                [
                    [
                        'child' => 'nested_child.txt',
                    ],
                ],
            ]
        );
    }
}
