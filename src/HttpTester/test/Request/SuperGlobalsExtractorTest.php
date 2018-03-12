<?php


namespace Draw\HttpTester\Request;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class SuperGlobalsExtractorTest extends TestCase
{
    /**
     * @var SuperGlobalsExtractor
     */
    private $superGlobalsExtractor;

    public function setUp()
    {
        $this->superGlobalsExtractor = new SuperGlobalsExtractor(null, 'gp');
    }

    public function testExtractSuperGlobals()
    {
        $request = new Request(
            'POST',
            'http://localhost/?key=value&duplicate=from_get',
            [
                'Content-Type' => $contentType = 'Content-Type: multipart/mixed; boundary=boundary',
                'Cookie' => $cookie = 'key1=value1; key2=value2'
            ],
            "------------boundary
Content-Disposition: form-data; name=field

value
------------boundary
Content-Disposition: form-data; name=duplicate

from_post
------------boundary
Content-Disposition: form-data; name=file; filename=filename.txt
Content-Type: text/plain

content of file
------------boundary"
        );

        $result = $this->superGlobalsExtractor->extractSuperGlobals($request);

        $this->assertTrue(isset($result['_FILES']['file']['tmp_name']));
        $tmp_name = $result['_FILES']['file']['tmp_name'];

        $this->assertSame(
            [
                '_SERVER' => [
                    'HTTP_HOST' => 'localhost',
                    'CONTENT_TYPE' => $contentType,
                    'HTTP_COOKIE' => $cookie,
                    'QUERY_STRING' => 'key=value&duplicate=from_get',
                    'REQUEST_URI' => '/',
                    'REQUEST_METHOD' => 'POST',
                ],
                '_POST' => ['field' => 'value', 'duplicate' => 'from_post'],
                '_GET' => ['key' => 'value', 'duplicate' => 'from_get'],
                '_COOKIE' => ['key1' => 'value1', 'key2' => 'value2'],
                '_FILES' => [
                    'file' => [
                        'name' => 'filename.txt',
                        'type' => 'text/plain',
                        'tmp_name' => $tmp_name,
                        'error' => UPLOAD_ERR_OK,
                        'size' => 15
                    ],
                ],
                '_REQUEST' => [
                    'key' => 'value',
                    'duplicate' => 'from_post',
                    'field' => 'value'
                ]
            ],
            $result
        );
    }
}