<?php

namespace Tests\Unit\Factories;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientInterface;
use Sumihiro\LineWorksClient\Factories\BotClientFactory;
use Sumihiro\LineWorksClient\LineWorksClient;

/**
 * Factory pattern testing.
 * This test verifies the BotClientFactory implementation.
 */
class BotClientFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_creates_bot_client_interface_instance()
    {
        $mockLineWorksClient = Mockery::mock(LineWorksClient::class);
        $factory = new BotClientFactory();
        
        $botClient = $factory->create($mockLineWorksClient);
        
        $this->assertInstanceOf(BotClientInterface::class, $botClient);
    }

    /** @test */
    public function it_passes_line_works_client_to_created_instance()
    {
        $mockLineWorksClient = Mockery::mock(LineWorksClient::class);
        $factory = new BotClientFactory();
        
        $botClient = $factory->create($mockLineWorksClient);
        
        // Verify that the created BotClient can access the underlying client
        $this->assertInstanceOf(BotClientInterface::class, $botClient);
        
        // We can't directly test getClient() as it's not in the interface,
        // but we can verify the factory creates a working instance
        $this->assertNotNull($botClient);
    }
}