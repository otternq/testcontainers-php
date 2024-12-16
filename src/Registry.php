<?php

declare(strict_types=1);

namespace Testcontainers;

use Testcontainers\Container\Container;

class Registry
{
    private static $registeredCleanup = false;

    /**
     * @var array<int|string, Container>
     */
    private static $registry = [];

    public static function add(Container $container)
    {
        self::$registry[spl_object_id($container)] = $container;

        if (!self::$registeredCleanup) {
            register_shutdown_function([self::class, 'cleanup']);
            self::$registeredCleanup = true;
        }
    }

    public static function remove(Container $container)
    {
        unset(self::$registry[spl_object_id($container)]);
    }

    public static function cleanup()
    {
        foreach (self::$registry as $container) {
            $container->remove();
        }
    }
}
