<?php

namespace Draw\HttpTester;

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

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return int
     */
    private function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * Assert that the response has a successful status code.
     *
     * @return $this
     */
    public function assertSuccessful()
    {
        $statusCode = (string)$this->getStatusCode();
        PHPUnit::assertTrue(
            strlen($statusCode) === 3 && in_array($statusCode{0}, [2, 3]),
            'Response status code [' . $this->getStatusCode() . '] is not a successful status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param  int $status
     * @return $this
     */
    public function assertStatus($status)
    {
        $actual = $this->getStatusCode();

        PHPUnit::assertSame(
            $status,
            $this->getStatusCode(),
            "Expected status code [{$status}] but received [{$actual}]."
        );

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param  string $uri
     * @return $this
     */
    public function assertRedirect($uri = null)
    {
        PHPUnit::assertTrue(
            in_array($this->getStatusCode(), [301, 302, 303, 307, 308]),
            'Response status code [' . $this->getStatusCode() . '] is not a redirect status code.'
        );

        if (!is_null($uri)) {
            PHPUnit::assertContains(
                $uri,
                $this->getResponse()->getHeader('location'),
                sprintf(
                    "The header location does not contain the uri [%s]. Values are:\n%s",
                    $uri,
                    implode("\n", $this->getResponse()->getHeader('location'))
                )
            );
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param  string $headerName
     * @param  mixed $value
     * @return $this
     */
    public function assertHeader($headerName, $value = null)
    {
        PHPUnit::assertTrue(
            $this->headers->has($headerName), "Header [{$headerName}] not present on response."
        );

        $actual = $this->headers->get($headerName);

        if (!is_null($value)) {
            PHPUnit::assertEquals(
                $value, $this->headers->get($headerName),
                "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
            );
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param  string $cookieName
     * @param  mixed $value
     * @return $this
     */
    public function assertPlainCookie($cookieName, $value = null)
    {
        $this->assertCookie($cookieName, $value, false);

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param  string $cookieName
     * @param  mixed $value
     * @param  bool $encrypted
     * @return $this
     */
    public function assertCookie($cookieName, $value = null, $encrypted = true)
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        if (!$cookie || is_null($value)) {
            return $this;
        }

        $cookieValue = $cookie->getValue();

        $actual = $encrypted
            ? app('encrypter')->decrypt($cookieValue) : $cookieValue;

        PHPUnit::assertEquals(
            $value, $actual,
            "Cookie [{$cookieName}] was found, but value [{$actual}] does not match [{$value}]."
        );

        return $this;
    }

    /**
     * Get the given cookie from the response.
     *
     * @param  string $cookieName
     * @return \Symfony\Component\HttpFoundation\Cookie|null
     */
    protected function getCookie($cookieName)
    {
        foreach ($this->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return $cookie;
            }
        }
    }
}