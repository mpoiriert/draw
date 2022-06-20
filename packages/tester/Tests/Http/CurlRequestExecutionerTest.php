<?php

namespace Draw\Component\Tester\Tests\Http;

use Draw\Component\Tester\Http\CurlRequestExecutioner;
use Draw\Component\Tester\Http\RequestExecutionerInterface;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class CurlRequestExecutionerTest extends TestCase
{
    public function testConstruct(): CurlRequestExecutioner
    {
        $curlRequestExecutioner = new CurlRequestExecutioner();

        static::assertInstanceOf(RequestExecutionerInterface::class, $curlRequestExecutioner);

        return $curlRequestExecutioner;
    }

    /**
     * @depends testConstruct
     */
    public function testExecuteRequest(CurlRequestExecutioner $curlRequestExecutioner): void
    {
        $request = new Request(
            'GET',
            'http://jsonplaceholder.typicode.com/posts/1'
        );

        $response = $curlRequestExecutioner->executeRequest($request);

        static::assertInstanceOf(ResponseInterface::class, $response);

        $content = $response->getBody()->getContents();

        static::assertJsonStringEqualsJsonString(
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
