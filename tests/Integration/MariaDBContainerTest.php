<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\MariaDBContainer;
use Testcontainers\Container\Container;

class MariaDBContainerTest extends TestCase
{
    public function testRun()
    {
        Container::$containerID = "mariadbcontainertest";

        $container = new MariaDBContainer();
        $container->withMariaDBDatabase('foo');
        $container->withMariaDBUser('bar', 'baz');

        $container->run();

        $pdo = new \PDO(
            sprintf('mysql:host=%s;port=3306', $container->getAddress()),
            'bar',
            'baz'
        );

        $query = $pdo->query('SHOW databases');

        $this->assertInstanceOf(\PDOStatement::class, $query);

        $databases = $query->fetchAll(\PDO::FETCH_COLUMN);

        $this->assertContains('foo', $databases);
    }
}