<?php

namespace Draw\Contracts\Application;

interface ConfigurationRegistryInterface
{
    /**
     * Return the configuration value or default if it doesn't exist.
     */
    public function get(string $name, mixed $default = null);

    /**
     * Set the configuration value.
     *
     * The value is expected to be json_encoded
     */
    public function set(string $name, mixed $value): void;

    /**
     * Check if the configuration base on the name exists or not.
     */
    public function has(string $name): bool;

    /**
     * Delete the configuration base on its name.
     */
    public function delete(string $name): void;
}
