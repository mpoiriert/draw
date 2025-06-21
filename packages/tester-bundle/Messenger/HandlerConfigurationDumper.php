<?php

namespace Draw\Bundle\TesterBundle\Messenger;

class HandlerConfigurationDumper
{
    public function __construct(private array $mapping = [])
    {
    }

    public function xmlDump(): string
    {
        $domDoc = new \DOMDocument('1.0', 'UTF-8');

        $domDoc->formatOutput = true;

        $mapping = $this->mapping;

        ksort($mapping);

        $bussesElement = $domDoc->createElement('busses');

        foreach ($mapping as $bus => $messages) {
            $busElement = $domDoc->createElement('bus');

            $busElement->setAttribute('name', $bus);

            ksort($messages);

            foreach ($messages as $message => $handlers) {
                $messageElement = $domDoc->createElement('message');
                $messageElement->setAttribute('name', $message);

                usort($handlers, static fn ($a, $b): int => $a[0] <=> $b[0]);

                foreach ($handlers as $handler) {
                    [$service, $options] = $handler;

                    $handlerElement = $domDoc->createElement('handler');
                    $handlerElement->setAttribute('service', $service);

                    ksort($options);

                    $options = array_merge(
                        ['method' => '_invoke'],
                        $options
                    );

                    foreach ($options as $key => $value) {
                        if ('method' === $key) {
                            $handlerElement->setAttribute('method', $value);
                        } else {
                            $handlerElement->setAttribute($key, (string) $value);
                        }
                    }

                    $messageElement->appendChild($handlerElement);
                }

                $busElement->appendChild($messageElement);
            }

            $bussesElement->appendChild($busElement);
        }

        $domDoc->appendChild($bussesElement);

        return $domDoc->saveXML();
    }
}
