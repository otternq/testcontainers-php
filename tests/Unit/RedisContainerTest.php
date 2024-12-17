<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\RedisContainer;
use Testcontainers\Container\Container;

class RedisContainerTest extends TestCase
{
    protected function tearDown()
    {
        Container::$containerID = "";
    }

    public function testGetCommandParams()
    {
        Container::$containerID = "testme";

        $container = new RedisContainer("latest");
        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "redis:latest"],
            $container->getCommandParams()
        );
    }
}