<?php

namespace Draw\Component\EntityMigrator\Workflow;

class MigrationWorkflow
{
    public const NAME = 'entity_migrator_migration';

    public const STATE_MACHINE_NAME = 'state_machine.'.self::NAME;

    public const PLACE_NEW = 'new';

    public const PLACE_PAUSED = 'paused';

    public const PLACE_PROCESSING = 'processing';

    public const PLACE_ERROR = 'error';

    public const PLACE_COMPLETED = 'completed';

    public const TRANSITION_PROCESS = 'process';

    public const TRANSITION_PAUSE = 'pause';

    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_ERROR = 'error';

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

    /**
     * Return the list of transition that are final.
     *
     * @return array<string>
     */
    public static function finalTransitions(): array
    {
        return [
            self::TRANSITION_COMPLETE,
            self::TRANSITION_ERROR,
        ];
    }
}
