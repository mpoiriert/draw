<?php

namespace Draw\Component\Tester\Http;

use Draw\Component\Tester\DataTester;
use Draw\Component\Tester\Http\Cookie\Cookie;
use PHPUnit\Framework\TestCase as PHPUnit;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestResponse
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Return the body contents of the response.
     *
     * Always seek the stream back to the beginning first in case of multiple call
     *
     * @return string
     */
    public function getResponseBodyContents(): ?string
    {
        $body = $this->getResponse()->getBody();
        $body->seek(0);

        return $body->getContents();
    }

    private function getStatusCode(): ?int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Assert that the response has a successful status code.
     *
     * @return $this
     */
    public function assertSuccessful(): self
    {
        $statusCode = (string) $this->getStatusCode();
        PHPUnit::assertTrue(
            3 === \strlen($statusCode) && \in_array(substr($statusCode, 0, 1), [2, 3]),
            'Response status code ['.$this->getStatusCode().'] is not a successful status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param int $status
     *
     * @return $this
     */
    public function assertStatus($status): self
    {
        $actual = $this->getStatusCode();

        PHPUnit::assertSame(
            $status,
            $this->getStatusCode(),
            sprintf(
                "Expected status code [%s] but received [%s]. Response Content:\n%s",
                $status,
                $actual,
                $this->getResponseBodyContents()
            )
        );

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param string $uri
     *
     * @return $this
     */
    public function assertRedirect($uri = null): self
    {
        PHPUnit::assertTrue(
            \in_array($this->getStatusCode(), [301, 302, 303, 307, 308]),
            'Response status code ['.$this->getStatusCode().'] is not a redirect status code.'
        );

        if (null !== $uri) {
            $this->assertHeader('location', $uri);
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param string $headerName
     * @param mixed  $value
     *
     * @return $this
     */
    public function assertHeader($headerName, $value = null): self
    {
        PHPUnit::assertTrue(
            $this->getResponse()->hasHeader($headerName),
            "Header [{$headerName}] not present on response."
        );

        if (null !== $value) {
            PHPUnit::assertContains(
                $value,
                $this->getResponse()->getHeader($headerName),
                sprintf(
                    "The header [%s] does not contain the value [%s]. Values are:\n%s",
                    $headerName,
                    $value,
                    implode("\n", $this->getResponse()->getHeader($headerName))
                )
            );
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param string $cookieName
     * @param mixed  $value
     *
     * @return $this
     */
    public function assertCookie($cookieName, $value = null): self
    {
        $cookies = $this->getCookies();
        $cookie = \array_key_exists($cookieName, $cookies) ? $cookies[$cookieName] : null;

        PHPUnit::assertNotNull(
            $cookie,
            sprintf(
                "Cookie [%s] not present on response. Possible cookies name are:\n%s",
                $cookieName,
                implode("\n", array_keys($cookies))
            )
        );

        if (null === $value) {
            return $this;
        }

        PHPUnit::assertEquals(
            $value,
            $cookieValue = $cookie->getValue(),
            "Cookie [{$cookieName}] was found, but value [{$cookieValue}] does not match [{$value}]."
        );

        return $this;
    }

    /**
     * @return Cookie[]
     */
    private function getCookies(): array
    {
        $cookies = [];
        if ($cookieHeader = $this->getResponse()->getHeader('Set-Cookie')) {
            foreach ($cookieHeader as $cookieString) {
                $cookie = Cookie::fromString($cookieString);
                $cookies[$cookie->getName()] = $cookie;
            }
        }

        return $cookies;
    }

    public function test(callable $test): self
    {
        (new DataTester($this))->test($test);

        return $this;
    }

    public function toJsonDataTester(): DataTester
    {
        return (new DataTester($this->getResponseBodyContents()))
            ->assertJson()
            ->transform('json_decode');
    }
}
