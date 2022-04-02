<?php

namespace Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor;

use Symfony\Component\HttpFoundation\RequestStack;

class RequestHeadersProcessor
{
    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    private ?RequestStack $requestStack;

    private ?array $onlyHeaders;

    private ?array $ignoreHeaders;

    private string $key;

    public function __construct(
        ?RequestStack $requestStack = null,
        array $onlyHeaders = [],
        array $ignoreHeaders = [],
        string $key = 'request_headers'
    ) {
        $this->requestStack = $requestStack;
        $this->key = $key;

        $this->onlyHeaders = array_flip(array_map([$this, 'normalizeHeaderName'], $onlyHeaders));
        $this->ignoreHeaders = array_flip(array_map([$this, 'normalizeHeaderName'], $ignoreHeaders));
    }

    public function __invoke(array $records): array
    {
        switch (true) {
            case null === $this->requestStack:
            case null === $request = $this->requestStack->getMainRequest():
                return $records;
        }

        $headers = $request->headers->all();

        if ($this->onlyHeaders) {
            $headers = array_intersect_key($headers, $this->onlyHeaders);
        }

        if ($this->ignoreHeaders) {
            $headers = array_diff_key($headers, $this->ignoreHeaders);
        }

        $records['extra'][$this->key] = $headers;

        return $records;
    }

    public function normalizeHeaderName(string $header): string
    {
        return strtr($header, self::UPPER, self::LOWER);
    }
}
