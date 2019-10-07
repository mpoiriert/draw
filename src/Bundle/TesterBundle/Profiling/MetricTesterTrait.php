<?php namespace Draw\Bundle\TesterBundle\Profiling;

use Draw\Bundle\TesterBundle\Http\RequestExecutioner;
use Draw\Component\Profiling\ProfilerCoordinator;
use Draw\Component\Tester\DataTester;
use Draw\Component\Tester\Http\ClientInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @method ClientInterface httpTester
 */
trait MetricTesterTrait
{
    public function assertMetrics($metricAssertions)
    {
        if (!is_array($metricAssertions)) {
            $metricAssertions = [$metricAssertions];
        }

        $requestExecutioner = $this->httpTester()->getRequestExecutioner();

        if(!$requestExecutioner instanceof RequestExecutioner) {
            Assert::fail(sprintf(
                'Incompatible Request Executioner. Make sure your request executioner is of type [%s].',
                RequestExecutioner::class
            ));
        }

        if(is_null($lastBrowser = $requestExecutioner->getLastBrowser())) {
            Assert::fail('No request executed to assert metrics.');
        }

        if(!$lastBrowser instanceof KernelBrowser) {
            Assert::fail(sprintf(
                'Incompatible Browser. Make sure the Last Browser if of type [%s].',
                KernelBrowser::class
            ));
        }

        $container = $lastBrowser->getContainer();

        $metrics = $container->get(ProfilerCoordinator::class)->stopAll();
        $tester = new DataTester($metrics);

        foreach ($metricAssertions as $metricAssertion) {
            $tester->test($metricAssertion);
        }
    }
}