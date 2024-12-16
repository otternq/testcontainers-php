<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\Connection\ConnectionException;
use Symfony\Component\Process\Process;
use Testcontainers\Container\Container;
use Testcontainers\Exception\ContainerNotReadyException;
use Testcontainers\Registry;
use Testcontainers\Traitt\DockerContainerAwareTrait;
use Testcontainers\Wait\WaitForExec;
use Testcontainers\Wait\WaitForHealthCheck;
use Testcontainers\Wait\WaitForHttp;
use Testcontainers\Wait\WaitForLog;
use Testcontainers\Wait\WaitForTcpPortOpen;

class WaitStrategyTest extends TestCase
{
    use DockerContainerAwareTrait;

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        Registry::cleanup();
    }

    public function testWaitForExec()
    {
        $called = false;

        $container = (new Container('mysql'))
            ->withEnvironment('MYSQL_ROOT_PASSWORD', 'root')
            ->withWait(new WaitForExec(['mysqladmin', 'ping', '-h', '127.0.0.1'], function (Process $process) use (&$called) {
                $called = true;
            }));

        $container->run();

        $this->assertTrue($called, 'Wait function was not called');
        unset($called);

        $pdo = new \PDO(
            sprintf('mysql:host=%s;port=3306', $container->getAddress()),
            'root',
            'root'
        );

        $query = $pdo->query('select version()');

        $this->assertInstanceOf(\PDOStatement::class, $query);

        $version = $query->fetchColumn();

        $this->assertNotEmpty($version);
    }

    public function testWaitForLog()
    {
        $container = (new Container('redis:6.2.5'))
            ->withWait(new WaitForLog('Ready to accept connections'));

        $container->run();

        $redis = new Client([
            'scheme' => 'tcp',
            'host'   => $container->getAddress(),
            'port'   => 6379,
        ]);

        $redis->set('foo', 'bar');

        $this->assertEquals('bar', $redis->get('foo'));

        $container->stop();

        $this->expectException(ConnectionException::class);

        $redis->get('foo');

        $container->remove();
    }

    public function testWaitForHTTP()
    {
        $container = (new Container('nginx:alpine'))
            ->withWait(WaitForHttp::make(80));

        $container->run();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('http://%s:%d', $container->getAddress(), 80));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = (string) curl_exec($ch);

        curl_close($ch);

        $this->assertNotEmpty($response);
    }

    /**
     * @dataProvider provideWaitForTcpPortOpen
     */
    public function testWaitForTcpPortOpen(bool $wait)
    {
        $container = new Container('nginx:alpine');

        if ($wait) {
            $container->withWait(WaitForTcpPortOpen::make(80));
        }

        $container->run();

        if ($wait) {
            static::assertIsResource(fsockopen($container->getAddress(), 80), 'Failed to connect to container');
            return;
        }

        $containerId = $container->getId();

        $this->expectExceptionObject(new ContainerNotReadyException($containerId));

        (new WaitForTcpPortOpen(8080))->wait($containerId);
    }

    /**
     * @return array<string, array<bool>>
     */
    public function provideWaitForTcpPortOpen()
    {
        return [
            'Can connect to container' => [true],
            'Cannot connect to container' => [false],
        ];
    }

    public function testWaitForHealthCheck()
    {
        $container = (new Container('nginx'))
            ->withHealthCheckCommand('curl --fail http://localhost')
            ->withWait(new WaitForHealthCheck());

        $container->run();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, sprintf('http://%s:%d', $container->getAddress(), 80));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        $this->assertNotEmpty($response);
        $this->assertIsString($response);

        $this->assertStringContainsString('Welcome to nginx!', $response);
    }
}
