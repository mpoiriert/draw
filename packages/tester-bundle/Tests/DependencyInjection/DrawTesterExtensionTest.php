<?php

namespace Draw\Bundle\TesterBundle\Tests\DependencyInjection;

use Draw\Bundle\TesterBundle\DependencyInjection\DrawTesterExtension;
use Draw\Bundle\TesterBundle\Messenger\HandleMessagesMappingProvider;
use Draw\Bundle\TesterBundle\Profiling\SqlProfiler;
use Draw\Component\Core\FilterExpression\Expression\CompositeExpressionEvaluator;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpressionEvaluator;
use Draw\Component\Profiling\ProfilerCoordinator;
use Draw\Component\Profiling\ProfilerInterface;
use Draw\Component\Tester\Test\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @internal
 */
class DrawTesterExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawTesterExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield [SqlProfiler::class];
        yield [\Draw\Component\Profiling\Sql\SqlProfiler::class, SqlProfiler::class];
        yield [ProfilerCoordinator::class];
        yield ['draw.tester.expression_filter'];
        yield [CompositeExpressionEvaluator::class];
        yield [ConstraintExpressionEvaluator::class];
        yield [HandleMessagesMappingProvider::class];
    }

    public function testProfilerInterfaceIsAutoConfigured(): void
    {
        $container = $this->load([]);
        $childDefinition = $container->getAutoconfiguredInstanceof()[ProfilerInterface::class];
        static::assertTrue($childDefinition->hasTag(ProfilerInterface::class));
    }
}
