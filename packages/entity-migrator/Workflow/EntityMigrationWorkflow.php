<?php

namespace Draw\Component\EntityMigrator\Workflow;

class EntityMigrationWorkflow
{
    public const NAME = 'entity_migrator_entity_migration';

    public const STATE_MACHINE_NAME = 'state_machine.'.self::NAME;

    public const PLACE_NEW = 'new';

    public const PLACE_PAUSED = 'paused';

    public const PLACE_PROCESSING = 'processing';

    public const PLACE_FAILED = 'failed';

    public const PLACE_QUEUED = 'queued';

    public const PLACE_COMPLETED = 'completed';

    public const PLACE_SKIPPED = 'skipped';

    public const TRANSITION_QUEUE = 'queue';

    public const TRANSITION_PROCESS = 'process';

    public const TRANSITION_PAUSE = 'pause';

    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_FAIL = 'fail';

    public const TRANSITION_REPROCESS = 'reprocess';

    public const TRANSITION_REQUEUE = 're_queue';

    public const TRANSITION_RETRY = 'retry';

    public const TRANSITION_SKIP = 'skip';

    /**
     * Return the places of the workflow.
     *
     * Use reflection to get the constants of the class.
     *
     * @return array<string>
     */
    public static function places(): array
    {
        $reflection = new \ReflectionClass(__CLASS__);

        return array_values(
            array_filter(
                $reflection->getConstants(),
                static fn ($constant) => str_starts_with($constant, 'PLACE_'),
                \ARRAY_FILTER_USE_KEY
            )
        );
    }

    public static function finalPlaces(): array
    {
        return [
            self::PLACE_COMPLETED,
            self::PLACE_FAILED,
            self::PLACE_SKIPPED,
        ];
    }
}
