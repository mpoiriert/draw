<?php

namespace Draw\Component\Tester\Tests\Http\Cookie;

use Draw\Component\Tester\Http\Cookie\Cookie;
use Draw\Component\Tester\Http\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CookieJarTest extends TestCase
{
    /** @var CookieJar */
    private $jar;

    protected function setUp(): void
    {
        $this->jar = new CookieJar();
    }

    protected function getTestCookies()
    {
        return [
            new Cookie([
                'Name' => 'foo',
                'Value' => 'bar',
                'Domain' => 'foo.com',
                'Path' => '/',
                'Discard' => true,
            ]),
            new Cookie([
                'Name' => 'test',
                'Value' => '123',
                'Domain' => 'baz.com',
                'Path' => '/foo',
                'Expires' => 2,
            ]),
            new Cookie([
                'Name' => 'you',
                'Value' => '123',
                'Domain' => 'bar.com',
                'Path' => '/boo',
                'Expires' => time() + 1000,
            ]),
        ];
    }

    public function testEmptyJarIsCountable()
    {
        static::assertCount(0, new CookieJar());
    }

    public function testGetsCookiesByName()
    {
        $cookies = $this->getTestCookies();
        foreach ($this->getTestCookies() as $cookie) {
            $this->jar->setCookie($cookie);
        }
        $testCookie = $cookies[0];
        static::assertEquals($testCookie, $this->jar->getCookieByName($testCookie->getName()));
        static::assertNull($this->jar->getCookieByName('doesnotexist'));
        static::assertNull($this->jar->getCookieByName(''));
        static::assertNull($this->jar->getCookieByName(null));
    }

    /**
     * Provides test data for cookie cookieJar retrieval.
     */
    public function getCookiesDataProvider()
    {
        return [
            [['foo', 'baz', 'test', 'muppet', 'googoo'], '', '', '', false],
            [['foo', 'baz', 'muppet', 'googoo'], '', '', '', true],
            [['googoo'], 'www.example.com', '', '', false],
            [['muppet', 'googoo'], 'test.y.example.com', '', '', false],
            [['foo', 'baz'], 'example.com', '', '', false],
            [['muppet'], 'x.y.example.com', '/acme/', '', false],
            [['muppet'], 'x.y.example.com', '/acme/test/', '', false],
            [['googoo'], 'x.y.example.com', '/test/acme/test/', '', false],
            [['foo', 'baz'], 'example.com', '', '', false],
            [['baz'], 'example.com', '', 'baz', false],
        ];
    }

    public function testStoresAndRetrievesCookies()
    {
        $cookies = $this->getTestCookies();
        foreach ($cookies as $cookie) {
            static::assertTrue($this->jar->setCookie($cookie));
        }
        static::assertCount(3, $this->jar);
        static::assertCount(3, $this->jar->getIterator());
        static::assertEquals($cookies, $this->jar->getIterator()->getArrayCopy());
    }

    public function testRemovesTemporaryCookies()
    {
        $cookies = $this->getTestCookies();
        foreach ($this->getTestCookies() as $cookie) {
            $this->jar->setCookie($cookie);
        }
        $this->jar->clearSessionCookies();
        static::assertEquals(
            [$cookies[1], $cookies[2]],
            $this->jar->getIterator()->getArrayCopy()
        );
    }

    public function testRemovesSelectively()
    {
        foreach ($this->getTestCookies() as $cookie) {
            $this->jar->setCookie($cookie);
        }
        // Remove foo.com cookies
        $this->jar->clear('foo.com');
        static::assertCount(2, $this->jar);
        // Try again, removing no further cookies
        $this->jar->clear('foo.com');
        static::assertCount(2, $this->jar);
        // Remove bar.com cookies with path of /boo
        $this->jar->clear('bar.com', '/boo');
        static::assertCount(1, $this->jar);
        // Remove cookie by name
        $this->jar->clear(null, null, 'test');
        static::assertCount(0, $this->jar);
    }

    public function testDoesNotAddIncompleteCookies()
    {
        static::assertFalse($this->jar->setCookie(new Cookie()));
        static::assertFalse($this->jar->setCookie(new Cookie([
            'Name' => 'foo',
        ])));
        static::assertFalse($this->jar->setCookie(new Cookie([
            'Name' => false,
        ])));
        static::assertFalse($this->jar->setCookie(new Cookie([
            'Name' => true,
        ])));
        static::assertFalse($this->jar->setCookie(new Cookie([
            'Name' => 'foo',
            'Domain' => 'foo.com',
        ])));
    }

    public function testDoesNotAddEmptyCookies()
    {
        static::assertFalse($this->jar->setCookie(new Cookie([
            'Name' => '',
            'Domain' => 'foo.com',
            'Value' => 0,
        ])));
    }

    public function testDoesAddValidCookies()
    {
        static::assertTrue($this->jar->setCookie(new Cookie([
            'Name' => '0',
            'Domain' => 'foo.com',
            'Value' => 0,
        ])));
        static::assertTrue($this->jar->setCookie(new Cookie([
            'Name' => 'foo',
            'Domain' => 'foo.com',
            'Value' => 0,
        ])));
        static::assertTrue($this->jar->setCookie(new Cookie([
            'Name' => 'foo',
            'Domain' => 'foo.com',
            'Value' => 0.0,
        ])));
        static::assertTrue($this->jar->setCookie(new Cookie([
            'Name' => 'foo',
            'Domain' => 'foo.com',
            'Value' => '0',
        ])));
    }

    public function testOverwritesCookiesThatAreOlderOrDiscardable()
    {
        $t = time() + 1000;
        $data = [
            'Name' => 'foo',
            'Value' => 'bar',
            'Domain' => '.example.com',
            'Path' => '/',
            'Max-Age' => '86400',
            'Secure' => true,
            'Discard' => true,
            'Expires' => $t,
        ];
        // Make sure that the discard cookie is overridden with the non-discard
        static::assertTrue($this->jar->setCookie(new Cookie($data)));
        static::assertCount(1, $this->jar);
        $data['Discard'] = false;
        static::assertTrue($this->jar->setCookie(new Cookie($data)));
        static::assertCount(1, $this->jar);
        /** @var Cookie[] $cookies */
        $cookies = $this->jar->getIterator()->getArrayCopy();
        static::assertFalse($cookies[0]->getDiscard());
        // Make sure it doesn't duplicate the cookie
        $this->jar->setCookie(new Cookie($data));
        static::assertCount(1, $this->jar);
        // Make sure the more future-ful expiration date supersede the other
        $data['Expires'] = time() + 2000;
        static::assertTrue($this->jar->setCookie(new Cookie($data)));
        static::assertCount(1, $this->jar);
        $cookies = $this->jar->getIterator()->getArrayCopy();
        static::assertNotEquals($t, $cookies[0]->getExpires());
    }

    public function testOverwritesCookiesThatHaveChanged()
    {
        $t = time() + 1000;
        $data = [
            'Name' => 'foo',
            'Value' => 'bar',
            'Domain' => '.example.com',
            'Path' => '/',
            'Max-Age' => '86400',
            'Secure' => true,
            'Discard' => true,
            'Expires' => $t,
        ];
        // Make sure that the discard cookie is overridden with the non-discard
        static::assertTrue($this->jar->setCookie(new Cookie($data)));
        $data['Value'] = 'boo';
        static::assertTrue($this->jar->setCookie(new Cookie($data)));
        static::assertCount(1, $this->jar);
        // Changing the value plus a parameter also must overwrite the existing one
        $data['Value'] = 'zoo';
        $data['Secure'] = false;
        static::assertTrue($this->jar->setCookie(new Cookie($data)));
        static::assertCount(1, $this->jar);
        /** @var Cookie[] $cookies */
        $cookies = $this->jar->getIterator()->getArrayCopy();
        static::assertEquals('zoo', $cookies[0]->getValue());
    }

    public function testAddsCookiesFromResponseWithRequest()
    {
        $response = new Response(200, [
            'Set-Cookie' => 'fpc=d=.Hm.yh4.1XmJWjJfs4orLQzKzPImxklQoxXSHOZATHUSEFciRueW_7704iYUtsXNEXq0M92Px2glMdWypmJ7HIQl6XIUvrZimWjQ3vIdeuRbI.FNQMAfcxu_XN1zSx7l.AcPdKL6guHc2V7hIQFhnjRW0rxm2oHY1P4bGQxFNz7f.tHm12ZD3DbdMDiDy7TBXsuP4DM-&v=2; expires=Fri, 02-Mar-2019 02:17:40 GMT;',
        ]);
        $request = new Request('GET', 'http://www.example.com');
        $this->jar->extractCookies($request, $response);
        static::assertCount(1, $this->jar);
    }

    public function getMatchingCookiesDataProvider()
    {
        return [
            ['https://example.com', 'foo=bar; baz=foobar'],
            ['http://example.com', ''],
            ['https://example.com:8912', 'foo=bar; baz=foobar'],
            ['https://foo.example.com', 'foo=bar; baz=foobar'],
            ['http://foo.example.com/test/acme/', 'googoo=gaga'],
        ];
    }

    /**
     * @dataProvider getMatchingCookiesDataProvider
     *
     * @param string $url
     * @param string $cookies
     */
    public function testReturnsCookiesMatchingRequests($url, $cookies)
    {
        $bag = [
            new Cookie([
                'Name' => 'foo',
                'Value' => 'bar',
                'Domain' => 'example.com',
                'Path' => '/',
                'Max-Age' => '86400',
                'Secure' => true,
            ]),
            new Cookie([
                'Name' => 'baz',
                'Value' => 'foobar',
                'Domain' => 'example.com',
                'Path' => '/',
                'Max-Age' => '86400',
                'Secure' => true,
            ]),
            new Cookie([
                'Name' => 'test',
                'Value' => '123',
                'Domain' => 'www.foobar.com',
                'Path' => '/path/',
                'Discard' => true,
            ]),
            new Cookie([
                'Name' => 'muppet',
                'Value' => 'cookie_monster',
                'Domain' => '.y.example.com',
                'Path' => '/acme/',
                'Expires' => time() + 86400,
            ]),
            new Cookie([
                'Name' => 'googoo',
                'Value' => 'gaga',
                'Domain' => '.example.com',
                'Path' => '/test/acme/',
                'Max-Age' => 1500,
            ]),
        ];
        foreach ($bag as $cookie) {
            $this->jar->setCookie($cookie);
        }
        $request = new Request('GET', $url);
        $request = $this->jar->withCookieHeader($request);
        static::assertEquals($cookies, $request->getHeaderLine('Cookie'));
    }

    public function testThrowsExceptionWithStrictMode()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid cookie: Cookie name must not contain invalid characters: ASCII Control characters (0-31;127), space, tab and the following characters: ()<>@,;:\"/?={}');
        $a = new CookieJar(true);
        $a->setCookie(new Cookie(['Name' => "abc\n", 'Value' => 'foo', 'Domain' => 'bar']));
    }

    public function testDeletesCookiesByName()
    {
        $cookies = $this->getTestCookies();
        $cookies[] = new Cookie([
            'Name' => 'other',
            'Value' => '123',
            'Domain' => 'bar.com',
            'Path' => '/boo',
            'Expires' => time() + 1000,
        ]);
        $jar = new CookieJar();
        foreach ($cookies as $cookie) {
            $jar->setCookie($cookie);
        }
        static::assertCount(4, $jar);
        $jar->clear('bar.com', '/boo', 'other');
        static::assertCount(3, $jar);
        $names = array_map(function (Cookie $c) {
            return $c->getName();
        }, $jar->getIterator()->getArrayCopy());
        static::assertEquals(['foo', 'test', 'you'], $names);
    }

    public function testAddsCookiesWithEmptyPathFromResponse()
    {
        $response = new Response(200, [
            'Set-Cookie' => 'fpc=foobar; expires=Fri, 02-Mar-'.(date('Y') + 1).' 02:17:40 GMT; path=;',
        ]);
        $request = new Request('GET', 'http://www.example.com');
        $this->jar->extractCookies($request, $response);
        $newRequest = $this->jar->withCookieHeader(new Request('GET', 'http://www.example.com/foo'));
        static::assertTrue($newRequest->hasHeader('Cookie'));
    }

    public function getCookiePathsDataProvider()
    {
        return [
            ['', '/'],
            ['/', '/'],
            ['/foo', '/'],
            ['/foo/bar', '/foo'],
            ['/foo/bar/', '/foo/bar'],
            ['foo', '/'],
            ['foo/bar', '/'],
            ['foo/bar/', '/'],
        ];
    }

    /**
     * @dataProvider getCookiePathsDataProvider
     *
     * @param string $uriPath
     * @param string $cookiePath
     */
    public function testCookiePathWithEmptyCookiePath($uriPath, $cookiePath)
    {
        $response = new Response(
            200,
            [
                'Set-Cookie' => [
                    'foo=bar; expires=Fri, 02-Mar-'.(date('Y') + 1).' 02:17:40 GMT; domain=www.example.com; path=;',
                    'bar=foo; expires=Fri, 02-Mar-'.(date('Y') + 1).' 02:17:40 GMT; domain=www.example.com; path=foobar;',
                ],
            ]
        );

        $request = new Request('GET', $uriPath, ['Host' => 'www.example.com']);
        $this->jar->extractCookies($request, $response);
        static::assertEquals($cookiePath, $this->jar->getCookieByName('foo')->getPath());
        static::assertEquals($cookiePath, $this->jar->getCookieByName('bar')->getPath());
    }
}
