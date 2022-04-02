<?php

namespace Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor;

use Symfony\Contracts\Service\ResetInterface;

class ChangeKeyProcessorDecorator implements ResetInterface
{
    private object $decoratedProcessor;

    private string $key;

    public function __construct(object $decoratedProcessor, string $key)
    {
        if (!method_exists($decoratedProcessor, '__invoke')) {
            throw new \InvalidArgumentException('Argument [$decoratedProcessor] is invalid. It must implement method [__invoke]');
        }
        $this->decoratedProcessor = $decoratedProcessor;
        $this->key = $key;
    }

    public function getDecoratedProcessor(): object
    {
        return $this->decoratedProcessor;
    }

    public function reset()
    {
        if ($this->decoratedProcessor instanceof ResetInterface) {
            $this->decoratedProcessor->reset();
        }
    }

    public function __invoke(array $records): array
    {
        $result = $this->decoratedProcessor->__invoke([]);
        if (isset($result['extra'])) {
            $records['extra'][$this->key] = reset($result['extra']);
        }

        return $records;
    }
}
