<?php

declare(strict_types=1);

namespace Testcontainers\Exception;

class ContainerNotReadyException extends \RuntimeException
{
    /**
     * @var string $id
     * @var Throwable $previous
     */
    public function __construct(string $id, $previous = null)
    {
        parent::__construct(sprintf('Container %s is not ready', $id), 0, $previous);
    }
}
