<?php

namespace App\Tests\Messenger\Retry;

use App\Tests\TestCase;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Messenger\Retry\EventDrivenRetryStrategy;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;

class EventDrivenRetryStrategyTest extends TestCase
{
    public function testRetry(): void
    {
        $service = static::getContainer()
            ->get('messenger.retry_strategy_locator')
            ->get('async');

        static::assertInstanceOf(
            EventDrivenRetryStrategy::class,
            $service
        );

        $fallbackStrategy = ReflectionAccessor::getPropertyValue(
            $service,
            'fallbackRetryStrategy'
        );

        static::assertInstanceOf(
            MultiplierRetryStrategy::class,
            $fallbackStrategy
        );
    }
}
