<?php

namespace Draw\Component\Tester\Http\Cookie;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Cookie jar that stores cookies as an array.
 */
class CookieJar implements CookieJarInterface
{
    /** @var Cookie[] Loaded cookie data */
    private array $cookies = [];

    /**
     * @param bool $strictMode set to true to throw exceptions when invalid
     *                         cookies are added to the cookie jar
     */
    public function __construct(private bool $strictMode = false)
    {
    }

    /**
     * Finds and returns the cookie based on the name.
     */
    public function getCookieByName(?string $name): ?Cookie
    {
        // don't allow a null name
        if (null === $name) {
            return null;
        }
        foreach ($this->cookies as $cookie) {
            if (null !== $cookie->getName() && 0 === strcasecmp($cookie->getName(), $name)) {
                return $cookie;
            }
        }

        return null;
    }

    public function clear($domain = null, $path = null, $name = null): self
    {
        if (!$domain) {
            $this->cookies = [];
        } elseif (!$path) {
            $this->cookies = array_filter(
                $this->cookies,
                fn (Cookie $cookie) => !$cookie->matchesDomain($domain)
            );
        } elseif (!$name) {
            $this->cookies = array_filter(
                $this->cookies,
                fn (Cookie $cookie) => !($cookie->matchesPath($path) && $cookie->matchesDomain($domain))
            );
        } else {
            $this->cookies = array_filter(
                $this->cookies,
                fn (Cookie $cookie) => !(
                    $cookie->getName() == $name
                    && $cookie->matchesPath($path)
                    && $cookie->matchesDomain($domain)
                )
            );
        }

        return $this;
    }

    public function clearSessionCookies(): void
    {
        $this->cookies = array_filter(
            $this->cookies,
            fn (Cookie $cookie) => !$cookie->getDiscard() && $cookie->getExpires()
        );
    }

    public function setCookie(Cookie $cookie): bool
    {
        // If the name string is empty (but not 0), ignore the set-cookie
        // string entirely.
        $name = $cookie->getName();
        if (!$name && '0' !== $name) {
            return false;
        }

        // Only allow cookies with set and valid domain, name, value
        $result = $cookie->validate();
        if (true !== $result) {
            if ($this->strictMode) {
                throw new \RuntimeException('Invalid cookie: '.$result);
            }
            $this->removeCookieIfEmpty($cookie);

            return false;
        }

        // Resolve conflicts with previously set cookies
        foreach ($this->cookies as $i => $c) {
            // Two cookies are identical, when their path, and domain are
            // identical.
            if ($c->getPath() != $cookie->getPath()
                || $c->getDomain() != $cookie->getDomain()
                || $c->getName() != $cookie->getName()
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

    public function count(): int
    {
        return \count($this->cookies);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator(array_values($this->cookies));
    }

    public function extractCookies(
        RequestInterface $request,
        ResponseInterface $response
    ): void {
        if ($cookieHeader = $response->getHeader('Set-Cookie')) {
            foreach ($cookieHeader as $cookie) {
                $sc = Cookie::fromString($cookie);
                if (!$sc->getDomain()) {
                    $sc->setDomain($request->getUri()->getHost());
                }
                if (!str_starts_with($sc->getPath(), '/')) {
                    $sc->setPath(static::getCookiePathFromRequest($request));
                }
                $this->setCookie($sc);
            }
        }
    }

    /**
     * Computes cookie path following RFC 6265 section 5.1.4.
     *
     * @see https://tools.ietf.org/html/rfc6265#section-5.1.4
     */
    public static function getCookiePathFromRequest(RequestInterface $request): string
    {
        $uriPath = $request->getUri()->getPath();
        if ('' === $uriPath) {
            return '/';
        }
        if (!str_starts_with($uriPath, '/')) {
            return '/';
        }
        if ('/' === $uriPath) {
            return '/';
        }
        if (0 === $lastSlashPos = strrpos($uriPath, '/')) {
            return '/';
        }

        return substr($uriPath, 0, $lastSlashPos);
    }

    public function withCookieHeader(RequestInterface $request): RequestInterface
    {
        $values = [];
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $path = $uri->getPath() ?: '/';

        foreach ($this->cookies as $cookie) {
            if ($cookie->matchesPath($path)
                && $cookie->matchesDomain($host)
                && !$cookie->isExpired()
                && (!$cookie->getSecure() || 'https' === $scheme)
            ) {
                $values[] = $cookie->getName().'='
                    .$cookie->getValue();
            }
        }

        return $values
            ? $request->withHeader('Cookie', implode('; ', $values))
            : $request;
    }

    /**
     * If a cookie already exists and the server asks to set it again with a
     * null value, the cookie must be deleted.
     */
    private function removeCookieIfEmpty(Cookie $cookie): void
    {
        if (empty($cookie->getValue())) {
            $this->clear(
                $cookie->getDomain(),
                $cookie->getPath(),
                $cookie->getName()
            );
        }
    }
}
