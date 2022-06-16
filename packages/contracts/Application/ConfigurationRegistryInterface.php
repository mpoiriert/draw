<?php

namespace Draw\Contracts\Application;

interface ConfigurationRegistryInterface
{
    /**
     * Return the configuration value or default if it doesn't exists.
     *
     * @param mixed|null $default
     */
    public function get(string $name, $default = null);

    /**
     * Set the configuration value.
     *
     * The value is expected to be json_encoded
     *
     * @param mixed $value
     */
    public function set(string $name, $value): void;

    /**
     * Check if the configuration base on the name exists or not.
     */
    public function has(string $name): bool;

    /**
     * Delete the configuration base on it's name.
     */
    public function delete(string $name): void;
}
