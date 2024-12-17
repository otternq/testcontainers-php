<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\MariaDBContainer;
use Testcontainers\Container\MySQLContainer;
use Testcontainers\Container\OpenSearchContainer;
use Testcontainers\Container\PostgresContainer;
use Testcontainers\Container\RedisContainer;
use Testcontainers\Container\Container;

class ContainerTest extends TestCase
{
    protected function tearDown()
    {
        Container::$containerID = "";
    }

    public function testGetCommandParams()
    {
        Container::$containerID = "testme";

        $container = new Container("something:latest");
        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "something:latest"],
            $container->getCommandParams()
        );
    }

    public function testGetCommandParamsWithMounts()
    {
        Container::$containerID = "testme";

        $container = (new Container("app:1"))
            ->withMount("LICENSE", "/tmp/LICENSE");

        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "-v", "LICENSE:/tmp/LICENSE", "app:1"],
            $container->getCommandParams()
        );
    }

    public function testGetCommandParamsWithPorts()
    {
        Container::$containerID = "testme";

        $container = (new Container("bob:1.0.1"))
            ->withPort("8080", "80");

        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "-p", "8080:80", "bob:1.0.1"],
            $container->getCommandParams()
        );
    }
}
