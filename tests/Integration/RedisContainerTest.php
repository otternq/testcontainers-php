<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\RedisContainer;
use Testcontainers\Container\Container;

class RedisContainerTest extends TestCase
{
    public function testRun()
    {
        Container::$containerID = "rediscontainertest";

        $container = new RedisContainer();

        $container->run();

        $redis = new Client([
            'scheme' => 'tcp',
            'host'   => $container->getAddress(),
            'port'   => 6379
        ]);

        $redis->ping();

        $this->assertTrue($redis->isConnected());
    }
}