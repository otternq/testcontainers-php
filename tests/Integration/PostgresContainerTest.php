<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\MariaDBContainer;
use Testcontainers\Container\MySQLContainer;
use Testcontainers\Container\OpenSearchContainer;
use Testcontainers\Container\PostgresContainer;
use Testcontainers\Container\RedisContainer;
use Testcontainers\Container\Container;

class PostgresContainerTest extends TestCase
{
    public function testRun()
    {
        if (!extension_loaded('postgresql')) {
            $this->markTestSkipped(
              'The postgresql extension is not available.'
            );
        }

        Container::$containerID = "postgrescontainertest";

        $container = (new PostgresContainer('latest', 'test'))
            ->withPostgresUser('test')
            ->withPostgresDatabase('foo')
            ->run();


        $pdo = new \PDO(
            sprintf('pgsql:host=%s;port=5432;dbname=foo', $container->getAddress()),
            'test',
            'test'
        );

        $query = $pdo->query('SELECT datname FROM pg_database');

        $this->assertInstanceOf(\PDOStatement::class, $query);

        $databases = $query->fetchAll(\PDO::FETCH_COLUMN);

        $this->assertContains('foo', $databases);
    }
}