<?php

namespace Draw\Bundle\TesterBundle\Messenger;

class HandleMessagesMappingProvider
{
    private $mappingByMessage;

    private $mappingByHandler;

    public function __construct(array $mapping = [])
    {
        ksort($mapping);
        $this->mappingByMessage = $mapping;
        $this->mappingByHandler = [];
        foreach ($mapping as $bus => $messages) {
            foreach ($messages as $message => $handlers) {
                foreach ($handlers as $handler) {
                    [$class, $options] = $handler;
                    $this->mappingByHandler[$class][$bus][$message][] = $options['method'] ?? '_invoke';
                    ksort($this->mappingByHandler[$class][$bus]);
                    sort($this->mappingByHandler[$class][$bus][$message]);
                }
            }
        }
    }

    /**
     * @return string[]
     */
    public function getBussesNames(): array
    {
        return array_keys($this->mappingByMessage);
    }

    public function getHandlerConfiguration(string $handlerClass): ?array
    {
        return $this->mappingByHandler[$handlerClass] ?? null;
    }
}
