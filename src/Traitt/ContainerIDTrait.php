<?php

declare(strict_types=1);

namespace Testcontainers\Traitt;

trait ContainerIDTrait
{
    public static $containerID;

    public static function getContainerID()
    {
        if (self::$containerID != null) {
            return self::$containerID;
        }

        return uniqid('testcontainer', true);
    }
}