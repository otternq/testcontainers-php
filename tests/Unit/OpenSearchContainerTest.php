<?php

declare(strict_types=1);

namespace Testcontainers\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Testcontainers\Container\OpenSearchContainer;
use Testcontainers\Container\Container;

class OpenSearchContainerTest extends TestCase
{
    protected function tearDown()
    {
        Container::$containerID = "";
    }

    public function testGetCommandParams()
    {
        Container::$containerID = "testme";

        $container = new OpenSearchContainer("latest");
        $this->assertEquals(
            ["docker", "run", "--rm", "--detach", "--name", "testme", "--env", "discovery.type=single-node", "--env", "OPENSEARCH_INITIAL_ADMIN_PASSWORD=c3o_ZPHo!", "opensearchproject/opensearch:latest"],
            $container->getCommandParams()
        );
    }
}