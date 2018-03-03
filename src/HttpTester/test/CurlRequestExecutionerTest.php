<?php

namespace Draw\HttpTester;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class CurlRequestExecutionerTest extends TestCase
{
    public function testConstruct()
    {
        $curlRequestExecutioner = new CurlRequestExecutioner();

        $this->assertInstanceOf(RequestExecutionerInterface::class, $curlRequestExecutioner);

        return $curlRequestExecutioner;
    }

    /**
     * @depends testConstruct
     *
     * @param CurlRequestExecutioner $curlRequestExecutioner
     */
    public function testExecuteRequest(CurlRequestExecutioner $curlRequestExecutioner)
    {
        $request = new Request(
            'GET',
            'http://jsonplaceholder.typicode.com/posts/1'
        );

        $response = $curlRequestExecutioner->executeRequest($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $content = $response->getBody()->getContents();

        $this->assertJsonStringEqualsJsonString(
            '{
  "userId": 1,
  "id": 1,
  "title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
  "body": "quia et suscipit\nsuscipit recusandae consequuntur expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
}',
            $content
        );
    }
}