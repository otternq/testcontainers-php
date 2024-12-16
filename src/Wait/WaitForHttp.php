<?php

declare(strict_types=1);

namespace Testcontainers\Wait;

use Testcontainers\Exception\ContainerNotReadyException;
use Testcontainers\Traitt\DockerContainerAwareTrait;

class WaitForHttp implements WaitInterface
{
    use DockerContainerAwareTrait;

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * @var string
     */
    private $method = 'GET';

    /**
     * @var string
     */
    private $path = '/';

    /**
     * @var int
     */
    private $statusCode = 200;

    /**
     * @var int
     */
    private $port;

    public function __construct(int $port)
    {
        $this->port = $port;
    }

    public static function make(int $port): self
    {
        return new WaitForHttp($port);
    }

    /**
     * @param WaitForHttp::METHOD_* $method
     */
    public function withMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function withPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function wait(string $id)
    {
        $containerAddress = self::dockerContainerAddress($id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('http://%s:%d%s', $containerAddress, $this->port, $this->path));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== $this->statusCode) {
            throw new ContainerNotReadyException($id, new \RuntimeException('HTTP status code does not match'));
        }

        curl_close($ch);
    }
}
