<?php

declare(strict_types=1);

namespace Testcontainers\Wait;

use Closure;
use Symfony\Component\Process\Process;
use Testcontainers\Exception\ContainerNotReadyException;

class WaitForExec implements WaitInterface
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var Callback
     */
    private $checkFunction;

    /**
     * @param array<string> $command
     * @param Closure $checkFunction
     */
    public function __construct(array $command, Closure $checkFunction = null)
    {
        $this->command = $command;
        $this->checkFunction = $checkFunction;
    }

    public function wait(string $id)
    {
        $process = new Process(array_merge(['docker', 'exec', $id], $this->command));

        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new ContainerNotReadyException($id, $e);
        }

        if ($this->checkFunction !== null) {
            $this->checkFunction($process);
        }
    }
}
