<?php

namespace Draw\Bundle\TesterBundle\Http;

use Draw\Component\Tester\Http\Client;
use Draw\Component\Tester\Http\ClientInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait HttpTesterTrait
{
    use \Draw\Component\Tester\HttpTesterTrait;

    protected function createHttpTesterClient(): ClientInterface
    {
        return new Client(new RequestExecutioner($this));
    }

    public function getClientContainer(ClientInterface $client = null): ContainerInterface
    {
        if (null === $client) {
            $client = self::$httpTesterClient;
        }

        /** @var RequestExecutioner $requestExecutioner */
        $requestExecutioner = $client->getRequestExecutioner();
        $lastBrowser = $requestExecutioner->getLastBrowser();
        if ($lastBrowser instanceof KernelBrowser) {
            return $lastBrowser->getContainer()->get('test.service_container');
        }

        throw new \RuntimeException(sprintf('Browser must be an instance of [%s]', KernelBrowser::class));
    }

    /**
     * @return object|null
     */
    public function getClientService(string $service)
    {
        return $this->getClientContainer()->get($service);
    }
}
