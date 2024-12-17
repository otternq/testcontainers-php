<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\PostgresContainer;
use Testcontainers\Container\Container;

class PostgresContainerTest extends TestCase
{
    protected function tearDown()
    {
        Container::$containerID = "";
    }

    public function testGetCommandParams()
    {
        Container::$containerID = "testme";

        $container = new PostgresContainer("latest", "test");
        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "--env", "POSTGRES_PASSWORD=test", "postgres:latest"],
            $container->getCommandParams()
        );
    }
}