<?php

namespace Draw\Contracts\Application;

use Draw\Contracts\Application\Exception\ConfigurationIsNotAccessibleException;

interface ConfigurationRegistryInterface
{
    /**
     * Return the configuration value or default if it doesn't exist.
     *
     * @throws ConfigurationIsNotAccessibleException
     */
    public function get(string $name, mixed $default = null);

    /**
     * Set the configuration value.
     *
     * The value is expected to be json_encoded
     *
     * @throws ConfigurationIsNotAccessibleException
     */
    public function set(string $name, mixed $value): void;

    /**
     * Check if the configuration base on the name exists or not.
     *
     * @throws ConfigurationIsNotAccessibleException
     */
    public function has(string $name): bool;

    /**
     * Delete the configuration base on its name.
     *
     * @throws ConfigurationIsNotAccessibleException
     */
    public function delete(string $name): void;
}
