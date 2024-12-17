<?php

declare(strict_types=1);

namespace Testcontainers\Wait;

use JsonException;
use RuntimeException;
use Testcontainers\Exception\ContainerNotReadyException;
use Testcontainers\Traitt\DockerContainerAwareTrait;

final class WaitForTcpPortOpen implements WaitInterface
{
    use DockerContainerAwareTrait;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $network;

    /**
     * @var int $port
     * @var string $network
     */
    public function __construct(int $port, string $network = null)
    {
        $this->port = $port;
        $this->network = $network;
    }

    /**
     * @var int $port
     * @var string $network
     */
    public static function make(int $port, string $network = null): self
    {
        return new self($port, $network);
    }

    /**
     * @throws JsonException
     */
    public function wait(string $id)
    {
        if (@fsockopen(self::dockerContainerAddress($id, $this->network), $this->port) === false) {
            throw new ContainerNotReadyException($id, new RuntimeException('Unable to connect to container TCP port'));
        }
    }
}
