<?php

declare(strict_types=1);

namespace Testcontainers\Container;

use Symfony\Component\Process\Process;
use Testcontainers\Exception\ContainerNotReadyException;
use Testcontainers\Registry;
use Testcontainers\Traitt\DockerContainerAwareTrait;
use Testcontainers\Traitt\ContainerIDTrait;
use Testcontainers\Wait\WaitForNothing;
use Testcontainers\Wait\WaitInterface;

/**
 * @phpstan-type ContainerInspectSingleNetwork array<int, array{'NetworkSettings': array{'IPAddress': string}}>
 * @phpstan-type ContainerInspectMultipleNetworks array<int, array{'NetworkSettings': array{'Networks': array<string, array{'IPAddress': string}>}}>
 * @phpstan-type ContainerInspect ContainerInspectSingleNetwork|ContainerInspectMultipleNetworks
 * @phpstan-type DockerNetwork array{CreatedAt: string, Driver: string, ID: string, IPv6: string, Internal: string, Labels: string, Name: string, Scope: string}
 */
class Container
{
    use DockerContainerAwareTrait;
    use ContainerIDTrait;

    /**
     * @var string
     */
    private $id;

    /**
     * @var ?string
     */
    private $entryPoint = null;

    /**
     * @var array<string, string>
     */
    private $env = [];

    /**
     * @var Process
     */
    private $process;
    /**
     * @var WaitInterface
     */
    private  $wait;

    /**
     * @var string
     */
    private $hostname = null;

    /**
     * @var bool
     */
    private $privileged = false;

    /**
     * @var string
     */
    private $network = null;

    /**
     * @var string
     */
    private $healthCheckCommand = null;

    /**
     * @var int
     */
    private $healthCheckIntervalInMS;

    /**
     * @var array<string>
     */
    private $cmd = [];

    /**
     * @var ContainerInspect
     */
    private $inspectedData;

    /**
     * @var array<string>
     */
    private $mounts = [];

    /**
     * @var array<string>
     */
    private $ports = [];

    /**
     * @var string
     */
    private $image;

    public function __construct(string $image)
    {
        $this->image = $image;
        $this->wait = new WaitForNothing();
    }

    public function getId(): string
    {
        if ($this->id !== null) {
            return $this->id;
        }

        $this->id = self::getContainerID();
        return $this->id;
    }

    public function withHostname(string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function withEntryPoint(string $entryPoint): self
    {
        $this->entryPoint = $entryPoint;

        return $this;
    }

    public function withEnvironment(string $name, string $value): self
    {
        $this->env[$name] = $value;

        return $this;
    }

    public function withImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function withWait(WaitInterface $wait): self
    {
        $this->wait = $wait;

        return $this;
    }

    public function withHealthCheckCommand(string $command, int $healthCheckIntervalInMS = 1000): self
    {
        $this->healthCheckCommand = $command;
        $this->healthCheckIntervalInMS = $healthCheckIntervalInMS;

        return $this;
    }

    /**
     * @param array<string> $cmd
     */
    public function withCmd(array $cmd): self
    {
        $this->cmd = $cmd;

        return $this;
    }

    public function withMount(string $localPath, string $containerPath): self
    {
        $this->mounts[] = '-v';
        $this->mounts[] = sprintf('%s:%s', $localPath, $containerPath);

        return $this;
    }

    public function withPort(string $localPort, string $containerPort): self
    {
        $this->ports[] = '-p';
        $this->ports[] = sprintf('%s:%s', $localPort, $containerPort);

        return $this;
    }

    public function withPrivileged(bool $privileged = true): self
    {
        $this->privileged = $privileged;

        return $this;
    }

    public function withNetwork(string $network): self
    {
        $this->network = $network;

        return $this;
    }

    public function run(bool $wait = true): self
    {
        $params = $this->getCommandParams();

        $this->process = new Process($params);
        $this->process->mustRun();

        $this->inspectedData = self::dockerContainerInspect($this->getId());

        Registry::add($this);

        if ($wait) {
            $this->wait();
        }

        return $this;
    }

    public function getCommandParams()
    {
        $params = array_merge(
            [
                'docker',
                'run',
                '--rm',
                '--detach',
                '--name',
                $this->getId()
            ],
            $this->mounts,
            $this->ports
        );

        foreach ($this->env as $name => $value) {
            $params[] = '--env';
            $params[] = $name . '=' . $value;
        }

        if ($this->healthCheckCommand !== null) {
            $params[] = '--health-cmd';
            $params[] = $this->healthCheckCommand;
            $params[] = '--health-interval';
            $params[] = $this->healthCheckIntervalInMS . 'ms';
        }

        if ($this->network !== null) {
            $params[] = '--network';
            $params[] = $this->network;
        }

        if ($this->hostname !== null) {
            $params[] = '--hostname';
            $params[] = $this->hostname;
        }

        if ($this->entryPoint !== null) {
            $params[] = '--entrypoint';
            $params[] = $this->entryPoint;
        }

        if ($this->privileged) {
            $params[] = '--privileged';
        }

        $params[] = $this->image;

        if (count($this->cmd) > 0) {
            array_push($params, ...$this->cmd);
        }

        return $params;
    }

    public function wait(int $wait = 100): self
    {
        $lastException = null;
        for ($i = 0; $i < $wait; $i++) {
            try {
                $this->wait->wait($this->getId());
                return $this;
            } catch (ContainerNotReadyException $e) {
                $lastException = $e;
                usleep(500000);
            }
        }

        throw new ContainerNotReadyException($this->getId(), $lastException->getPrevious());
    }

    public function stop(): self
    {
        $stop = new Process(['docker', 'stop', $this->getId()]);
        $stop->mustRun();

        return $this;
    }

    public function start(): self
    {
        $start = new Process(['docker', 'start', $this->getId()]);
        $start->mustRun();

        return $this;
    }

    public function restart(): self
    {
        $restart = new Process(['docker', 'restart', $this->getId()]);
        $restart->mustRun();

        return $this;
    }

    public function remove(): self
    {
        $remove = new Process(['docker', 'rm', '-f', $this->getId()]);
        $remove->mustRun();

        Registry::remove($this);

        return $this;
    }

    public function kill(): self
    {
        $kill = new Process(['docker', 'kill', $this->getId()]);
        $kill->mustRun();

        return $this;
    }

    /**
     * @param array<string> $command
     */
    public function execute(array $command): Process
    {
        $process = new Process(array_merge(['docker', 'exec', $this->getId()], $command));
        $process->mustRun();

        return $process;
    }

    public function logs(): string
    {
        $logs = new Process(['docker', 'logs', $this->getId()]);
        $logs->mustRun();

        return $logs->getOutput();
    }

    public function getAddress(): string
    {
        return self::dockerContainerAddress(
            $this->getId(),
            $this->network,
            $this->inspectedData
        );
    }
}
