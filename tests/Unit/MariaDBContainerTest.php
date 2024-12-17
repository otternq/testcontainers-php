<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\MariaDBContainer;
use Testcontainers\Container\Container;

class MariaDBContainerTest extends TestCase
{
    protected function tearDown()
    {
        Container::$containerID = "";
    }

    public function testGetCommandParams()
    {
        Container::$containerID = "testme";

        $container = new MariaDBContainer("latest");
        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "--env", "MARIADB_ROOT_PASSWORD=root", "mariadb:latest"],
            $container->getCommandParams()
        );
    }
}
