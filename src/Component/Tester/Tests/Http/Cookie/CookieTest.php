<?php

namespace Draw\Component\Tester\Tests\Http\Cookie;

use Draw\Component\Tester\Http\Cookie\Cookie;
use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    public function testInitializesDefaultValues()
    {
        $cookie = new Cookie();
        $this->assertEquals('/', $cookie->getPath());
    }

    public function testConvertsDateTimeMaxAgeToUnixTimestamp()
    {
        $cookie = new Cookie(['Expires' => 'November 20, 1984']);
        $this->assertIsInt($cookie->getExpires());
    }

    public function testAddsExpiresBasedOnMaxAge()
    {
        $t = time();
        $cookie = new Cookie(['Max-Age' => 100]);
        $this->assertEquals($t + 100, $cookie->getExpires());
    }

    public function testHoldsValues()
    {
        $t = time();
        $data = [
            'Name' => 'foo',
            'Value' => 'baz',
            'Path' => '/bar',
            'Domain' => 'baz.com',
            'Expires' => $t,
            'Max-Age' => 100,
            'Secure' => true,
            'Discard' => true,
            'HttpOnly' => true,
            'foo' => 'baz',
            'bar' => 'bam',
        ];

        $cookie = new Cookie($data);
        $this->assertEquals($data, $cookie->toArray());

        $this->assertEquals('foo', $cookie->getName());
        $this->assertEquals('baz', $cookie->getValue());
        $this->assertEquals('baz.com', $cookie->getDomain());
        $this->assertEquals('/bar', $cookie->getPath());
        $this->assertEquals($t, $cookie->getExpires());
        $this->assertEquals(100, $cookie->getMaxAge());
        $this->assertTrue($cookie->getSecure());
        $this->assertTrue($cookie->getDiscard());
        $this->assertTrue($cookie->getHttpOnly());
        $this->assertEquals('baz', $cookie->toArray()['foo']);
        $this->assertEquals('bam', $cookie->toArray()['bar']);

        $cookie->setName('a');
        $cookie->setValue('b');
        $cookie->setPath('c');
        $cookie->setDomain('bar.com');
        $cookie->setExpires(10);
        $cookie->setMaxAge(200);
        $cookie->setSecure(false);
        $cookie->setHttpOnly(false);
        $cookie->setDiscard(false);

        $this->assertEquals('a', $cookie->getName());
        $this->assertEquals('b', $cookie->getValue());
        $this->assertEquals('c', $cookie->getPath());
        $this->assertEquals('bar.com', $cookie->getDomain());
        $this->assertEquals(10, $cookie->getExpires());
        $this->assertEquals(200, $cookie->getMaxAge());
        $this->assertFalse($cookie->getSecure());
        $this->assertFalse($cookie->getDiscard());
        $this->assertFalse($cookie->getHttpOnly());
    }

    public function testDeterminesIfExpired()
    {
        $c = new Cookie();
        $c->setExpires(10);
        $this->assertTrue($c->isExpired());
        $c->setExpires(time() + 10000);
        $this->assertFalse($c->isExpired());
    }

    public function testMatchesDomain()
    {
        $cookie = new Cookie();
        $this->assertTrue($cookie->matchesDomain('baz.com'));

        $cookie->setDomain('baz.com');
        $this->assertTrue($cookie->matchesDomain('baz.com'));
        $this->assertFalse($cookie->matchesDomain('bar.com'));

        $cookie->setDomain('.baz.com');
        $this->assertTrue($cookie->matchesDomain('.baz.com'));
        $this->assertTrue($cookie->matchesDomain('foo.baz.com'));
        $this->assertFalse($cookie->matchesDomain('baz.bar.com'));
        $this->assertTrue($cookie->matchesDomain('baz.com'));

        $cookie->setDomain('.127.0.0.1');
        $this->assertTrue($cookie->matchesDomain('127.0.0.1'));

        $cookie->setDomain('127.0.0.1');
        $this->assertTrue($cookie->matchesDomain('127.0.0.1'));

        $cookie->setDomain('.com.');
        $this->assertFalse($cookie->matchesDomain('baz.com'));

        $cookie->setDomain('.local');
        $this->assertTrue($cookie->matchesDomain('example.local'));
    }

    public function pathMatchProvider()
    {
        return [
            ['/foo', '/foo', true],
            ['/foo', '/Foo', false],
            ['/foo', '/fo', false],
            ['/foo', '/foo/bar', true],
            ['/foo', '/foo/bar/baz', true],
            ['/foo', '/foo/bar//baz', true],
            ['/foo', '/foobar', false],
            ['/foo/bar', '/foo', false],
            ['/foo/bar', '/foobar', false],
            ['/foo/bar', '/foo/bar', true],
            ['/foo/bar', '/foo/bar/', true],
            ['/foo/bar', '/foo/bar/baz', true],
            ['/foo/bar/', '/foo/bar', false],
            ['/foo/bar/', '/foo/bar/', true],
            ['/foo/bar/', '/foo/bar/baz', true],
        ];
    }

    /**
     * @dataProvider pathMatchProvider
     *
     * @param string $cookiePath
     * @param string $requestPath
     * @param bool   $isMatch
     */
    public function testMatchesPath($cookiePath, $requestPath, $isMatch)
    {
        $cookie = new Cookie();
        $cookie->setPath($cookiePath);
        $this->assertEquals($isMatch, $cookie->matchesPath($requestPath));
    }

    public function cookieValidateProvider()
    {
        return [
            ['foo', 'baz', 'bar', true],
            ['0', '0', '0', true],
            ['foo[bar]', 'baz', 'bar', true],
            ['', 'baz', 'bar', 'The cookie name must not be empty'],
            ['foo', '', 'bar', 'The cookie value must not be empty'],
            ['foo', 'baz', '', 'The cookie domain must not be empty'],
            ["foo\r", 'baz', '0', 'Cookie name must not contain invalid characters: ASCII Control characters (0-31;127), space, tab and the following characters: ()<>@,;:\"/?={}'],
        ];
    }

    /**
     * @dataProvider cookieValidateProvider
     *
     * @param string      $name
     * @param string      $value
     * @param string      $domain
     * @param bool|string $result
     */
    public function testValidatesCookies($name, $value, $domain, $result)
    {
        $cookie = new Cookie([
            'Name' => $name,
            'Value' => $value,
            'Domain' => $domain,
        ]);
        $this->assertSame($result, $cookie->validate());
    }

    public function testDoesNotMatchIp()
    {
        $cookie = new Cookie(['Domain' => '192.168.16.']);
        $this->assertFalse($cookie->matchesDomain('192.168.16.121'));
    }

    public function testConvertsToString()
    {
        $t = 1382916008;
        $cookie = new Cookie([
            'Name' => 'test',
            'Value' => '123',
            'Domain' => 'foo.com',
            'Expires' => $t,
            'Path' => '/abc',
            'HttpOnly' => true,
            'Secure' => true,
        ]);
        $this->assertEquals(
            'test=123; Domain=foo.com; Path=/abc; Expires=Sun, 27 Oct 2013 23:20:08 GMT; Secure; HttpOnly',
            (string) $cookie
        );
    }

    /**
     * Provides the parsed information from a cookie.
     *
     * @return array
     */
    public function cookieParserDataProvider()
    {
        return [
            [
                'ASIHTTPRequestTestCookie=This+is+the+value; expires=Sat, 26-Jul-2008 17:00:42 GMT; path=/tests; domain=allseeing-i.com; PHPSESSID=6c951590e7a9359bcedde25cda73e43c; path=/;',
                [
                    'Domain' => 'allseeing-i.com',
                    'Path' => '/',
                    'PHPSESSID' => '6c951590e7a9359bcedde25cda73e43c',
                    'Max-Age' => null,
                    'Expires' => 'Sat, 26-Jul-2008 17:00:42 GMT',
                    'Secure' => null,
                    'Discard' => null,
                    'Name' => 'ASIHTTPRequestTestCookie',
                    'Value' => 'This+is+the+value',
                    'HttpOnly' => false,
                ],
            ],
            ['', []],
            ['foo', []],
            [
                'foo="bar"',
                [
                    'Name' => 'foo',
                    'Value' => '"bar"',
                    'Discard' => null,
                    'Domain' => null,
                    'Expires' => null,
                    'Max-Age' => null,
                    'Path' => '/',
                    'Secure' => null,
                    'HttpOnly' => false,
                ],
            ],
            // Test setting a blank value for a cookie
            [[
                'foo=', 'foo =', 'foo =;', 'foo= ;', 'foo =', 'foo= ', ],
                [
                    'Name' => 'foo',
                    'Value' => '',
                    'Discard' => null,
                    'Domain' => null,
                    'Expires' => null,
                    'Max-Age' => null,
                    'Path' => '/',
                    'Secure' => null,
                    'HttpOnly' => false,
                ],
            ],
            // Test setting a value and removing quotes
            [[
                'foo=1', 'foo =1', 'foo =1;', 'foo=1 ;', 'foo =1', 'foo= 1', 'foo = 1 ;', ],
                [
                    'Name' => 'foo',
                    'Value' => '1',
                    'Discard' => null,
                    'Domain' => null,
                    'Expires' => null,
                    'Max-Age' => null,
                    'Path' => '/',
                    'Secure' => null,
                    'HttpOnly' => false,
                ],
            ],
            // Some of the following tests are based on http://framework.zend.com/svn/framework/standard/trunk/tests/Zend/Http/CookieTest.php
            [
                'justacookie=foo; domain=example.com',
                [
                    'Name' => 'justacookie',
                    'Value' => 'foo',
                    'Domain' => 'example.com',
                    'Discard' => null,
                    'Expires' => null,
                    'Max-Age' => null,
                    'Path' => '/',
                    'Secure' => null,
                    'HttpOnly' => false,
                ],
            ],
            [
                'expires=tomorrow; secure; path=/Space Out/; expires=Tue, 21-Nov-2006 08:33:44 GMT; domain=.example.com',
                [
                    'Name' => 'expires',
                    'Value' => 'tomorrow',
                    'Domain' => '.example.com',
                    'Path' => '/Space Out/',
                    'Expires' => 'Tue, 21-Nov-2006 08:33:44 GMT',
                    'Discard' => null,
                    'Secure' => true,
                    'Max-Age' => null,
                    'HttpOnly' => false,
                ],
            ],
            [
                'domain=unittests; expires=Tue, 21-Nov-2006 08:33:44 GMT; domain=example.com; path=/some value/',
                [
                    'Name' => 'domain',
                    'Value' => 'unittests',
                    'Domain' => 'example.com',
                    'Path' => '/some value/',
                    'Expires' => 'Tue, 21-Nov-2006 08:33:44 GMT',
                    'Secure' => false,
                    'Discard' => null,
                    'Max-Age' => null,
                    'HttpOnly' => false,
                ],
            ],
            [
                'path=indexAction; path=/; domain=.foo.com; expires=Tue, 21-Nov-2006 08:33:44 GMT',
                [
                    'Name' => 'path',
                    'Value' => 'indexAction',
                    'Domain' => '.foo.com',
                    'Path' => '/',
                    'Expires' => 'Tue, 21-Nov-2006 08:33:44 GMT',
                    'Secure' => false,
                    'Discard' => null,
                    'Max-Age' => null,
                    'HttpOnly' => false,
                ],
            ],
            [
                'secure=sha1; secure; SECURE; domain=some.really.deep.domain.com; version=1; Max-Age=86400',
                [
                    'Name' => 'secure',
                    'Value' => 'sha1',
                    'Domain' => 'some.really.deep.domain.com',
                    'Path' => '/',
                    'Secure' => true,
                    'Discard' => null,
                    'Expires' => time() + 86400,
                    'Max-Age' => 86400,
                    'HttpOnly' => false,
                    'version' => '1',
                ],
            ],
            [
                'PHPSESSID=123456789+abcd%2Cef; secure; discard; domain=.localdomain; path=/foo/baz; expires=Tue, 21-Nov-2006 08:33:44 GMT;',
                [
                    'Name' => 'PHPSESSID',
                    'Value' => '123456789+abcd%2Cef',
                    'Domain' => '.localdomain',
                    'Path' => '/foo/baz',
                    'Expires' => 'Tue, 21-Nov-2006 08:33:44 GMT',
                    'Secure' => true,
                    'Discard' => true,
                    'Max-Age' => null,
                    'HttpOnly' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider cookieParserDataProvider
     *
     * @param string $cookie
     * @param array  $parsed
     */
    public function testParseCookie($cookie, $parsed)
    {
        foreach ((array) $cookie as $v) {
            $c = Cookie::fromString($v);
            $p = $c->toArray();

            if (isset($p['Expires'])) {
                // Remove expires values from the assertion if they are relatively equal
                if (abs($p['Expires'] != strtotime($parsed['Expires'])) < 40) {
                    unset($p['Expires']);
                    unset($parsed['Expires']);
                }
            }

            if (!empty($parsed)) {
                foreach ($parsed as $key => $value) {
                    $this->assertEquals($parsed[$key], $p[$key], 'Comparing '.$key.' '.var_export($value, true).' : '.var_export($parsed, true).' | '.var_export($p, true));
                }
                foreach ($p as $key => $value) {
                    $this->assertEquals($p[$key], $parsed[$key], 'Comparing '.$key.' '.var_export($value, true).' : '.var_export($parsed, true).' | '.var_export($p, true));
                }
            } else {
                $this->assertEquals([
                    'Name' => null,
                    'Value' => null,
                    'Domain' => null,
                    'Path' => '/',
                    'Max-Age' => null,
                    'Expires' => null,
                    'Secure' => false,
                    'Discard' => false,
                    'HttpOnly' => false,
                ], $p);
            }
        }
    }
}
