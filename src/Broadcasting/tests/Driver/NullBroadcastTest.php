<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting\Driver;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Broadcasting\Driver\NullBroadcast;

final class NullBroadcastTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private NullBroadcast $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = new NullBroadcast();
    }

    public function testAuthorize(): void
    {
        $this->assertTrue(
            $this->driver->authorize(m::mock(ServerRequestInterface::class))
        );
    }

    public function testPublishMessageToTopic(): void
    {
        $this->driver->publish('topic', 'message');
        $this->assertTrue(true);
    }
}
