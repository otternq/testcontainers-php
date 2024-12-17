<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\MySQLContainer;
use Testcontainers\Container\Container;

class MySQLContainerTest extends TestCase
{
    public function testRun()
    {
        $this->markTestSkipped(
            'Skipping until I can work through mounting config to allow php 7.0'
        );

        Container::$containerID = "mysqlcontainertest";

        $container = (new MySQLContainer())
            ->withMySQLDatabase('foo')
            ->withMySQLUser('bar', 'baz')
            ->withPort("3306", "3306");

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