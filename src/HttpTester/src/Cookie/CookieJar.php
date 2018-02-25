<?php

namespace Draw\HttpTester\Cookie;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Cookie jar that stores cookies an an array
 */
class CookieJar implements CookieJarInterface
{
    /** @var Cookie[] Loaded cookie data */
    private $cookies = [];

    /** @var bool */
    private $strictMode;

    /**
     * @param bool $strictMode   Set to true to throw exceptions when invalid
     *                           cookies are added to the cookie jar.
     * @param Cookie[] $cookies
     */
    public function __construct($strictMode = false, array $cookies = [])
    {
        $this->strictMode = $strictMode;

        foreach ($cookies as $cookie) {
            $this->setCookie($cookie);
        }
    }

    /**
     * Quote the cookie value if it is not already quoted and it contains
     * problematic characters.
     *
     * @param string $value Value that may or may not need to be quoted
     *
     * @return string
     */
    public static function getCookieValue($value)
    {
        if (substr($value, 0, 1) !== '"' &&
            substr($value, -1, 1) !== '"' &&
            strpbrk($value, ';,')
        ) {
            $value = '"' . $value . '"';
        }

        return $value;
    }

    public function clear($domain = null, $path = null, $name = null)
    {
        if (!$domain) {
            $this->cookies = [];
            return;
        }

        if (!$path) {
            $this->cookies = array_filter(
                $this->cookies,
                function (Cookie $cookie) use ($path, $domain) {
                    return !$cookie->matchesDomain($domain);
                }
            );
            return;
        }

        if (!$name) {
            $this->cookies = array_filter(
                $this->cookies,
                function (Cookie $cookie) use ($path, $domain) {
                    return !($cookie->matchesPath($path) &&
                        $cookie->matchesDomain($domain));
                }
            );
            return;
        }

        $this->cookies = array_filter(
            $this->cookies,
            function (Cookie $cookie) use ($path, $domain, $name) {
                return !($cookie->getName() == $name &&
                    $cookie->matchesPath($path) &&
                    $cookie->matchesDomain($domain));
            }
        );
    }

    public function clearSessionCookies()
    {
        $this->cookies = array_filter(
            $this->cookies,
            function (Cookie $cookie) {
                return !$cookie->getDiscard() && $cookie->getExpires();
            }
        );
    }

    public function setCookie(Cookie $cookie)
    {
        // Only allow cookies with set and valid domain, name, value
        $result = $cookie->validate();
        if ($result !== true) {
            if ($this->strictMode) {
                throw new \RuntimeException('Invalid cookie: ' . $result);
            } else {
                $this->removeCookieIfEmpty($cookie);
                return false;
            }
        }

        // Resolve conflicts with previously set cookies
        foreach ($this->cookies as $i => $c) {

            // Two cookies are identical, when their path, and domain are
            // identical.
            if ($c->getPath() != $cookie->getPath() ||
                $c->getDomain() != $cookie->getDomain() ||
                $c->getName() != $cookie->getName()
            ) {
                continue;
            }

            // The previously set cookie is a discard cookie and this one is
            // not so allow the new cookie to be set
            if (!$cookie->getDiscard() && $c->getDiscard()) {
                unset($this->cookies[$i]);
                continue;
            }

            // If the new cookie's expiration is further into the future, then
            // replace the old cookie
            if ($cookie->getExpires() > $c->getExpires()) {
                unset($this->cookies[$i]);
                continue;
            }

            // If the value has changed, we better change it
            if ($cookie->getValue() !== $c->getValue()) {
                unset($this->cookies[$i]);
                continue;
            }

            // The cookie exists, so no need to continue
            return false;
        }

        $this->cookies[] = $cookie;

        return true;
    }

    public function count()
    {
        return count($this->cookies);
    }

    public function getIterator()
    {
        return new \ArrayIterator(array_values($this->cookies));
    }

    public function extractCookies(RequestInterface $request, ResponseInterface $response) {
        foreach ($response->getHeader('Set-Cookie') as $cookie) {
            $cookie = Cookie::fromString($cookie);
            if (!$cookie->getDomain()) {
                $cookie->setDomain($request->getUri()->getHost());
            }
            $this->setCookie($cookie);
        }
    }

    /**
     * @param RequestInterface $request
     * @return MessageInterface
     */
    public function addCookieHeader(RequestInterface $request)
    {
        if(!$this->cookies) {
            return $request;
        }

        $values = [];
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $path = $uri->getPath();

        foreach ($this->cookies as $cookie) {
            switch(true) {
                case !$cookie->matchesPath($path):
                case !$cookie->matchesDomain($host):
                case $cookie->isExpired():
                case $cookie->getSecure() && $scheme !== 'https':
                    continue;
            }

            $values[] = $cookie->getName() . '=' . self::getCookieValue($cookie->getValue());
        }

        return $request->withAddedHeader('Cookie', implode('; ', $values));
    }

    /**
     * If a cookie already exists and the server asks to set it again with a
     * null value, the cookie must be deleted.
     *
     * @param Cookie $cookie
     */
    private function removeCookieIfEmpty(Cookie $cookie)
    {
        $cookieValue = $cookie->getValue();
        if ($cookieValue === null || $cookieValue === '') {
            $this->clear(
                $cookie->getDomain(),
                $cookie->getPath(),
                $cookie->getName()
            );
        }
    }
}