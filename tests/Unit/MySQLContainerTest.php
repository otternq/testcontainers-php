<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\MySQLContainer;
use Testcontainers\Container\Container;

class MySQLContainerTest extends TestCase
{
    protected function tearDown()
    {
        Container::$containerID = "";
    }

    public function testGetCommandParams_defaultConstructor()
    {
        Container::$containerID = "testme";

        $container = new MySQLContainer();

        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "--env", "MYSQL_ROOT_PASSWORD=root", "mysql:latest"],
            $container->getCommandParams()
        );
    }

    public function testGetCommandParams_constructorOverrideValues()
    {
        Container::$containerID = "testme";

        $container = new MySQLContainer("10.1", "boot");

        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "--env", "MYSQL_ROOT_PASSWORD=boot", "mysql:10.1"],
            $container->getCommandParams()
        );
    }

    public function testGetCommandParams_withMySQLUser()
    {
        Container::$containerID = "testme";

        $container = (new MySQLContainer())
            ->withMySQLUser("user", "pass");

        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "--env", "MYSQL_ROOT_PASSWORD=root", "--env", "MYSQL_USER=user", "--env", "MYSQL_PASSWORD=pass", "mysql:latest"],
            $container->getCommandParams()
        );
    }

    public function testGetCommandParams_withMySQLDatabase()
    {
        Container::$containerID = "testme";

        $container = (new MySQLContainer())
            ->withMySQLDatabase("data");

        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "--env", "MYSQL_ROOT_PASSWORD=root", "--env", "MYSQL_DATABASE=data", "mysql:latest"],
            $container->getCommandParams()
        );
    }

    public function testGetCommandParams_withPort()
    {
        Container::$containerID = "testme";

        $container = (new MySQLContainer())
            ->withPort("3306", "3306");

        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "-p", "3306:3306", "--env", "MYSQL_ROOT_PASSWORD=root", "mysql:latest"],
            $container->getCommandParams()
        );
    }
}
