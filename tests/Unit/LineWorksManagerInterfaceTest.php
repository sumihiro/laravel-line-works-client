<?php

namespace Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;
use Illuminate\Contracts\Container\Container;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientFactoryInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientInterface;
use Sumihiro\LineWorksClient\LineWorksClient;
use Sumihiro\LineWorksClient\LineWorksManager;

/**
 * LineWorksManager interface integration testing.
 * This test demonstrates the new factory injection capabilities.
 */
class LineWorksManagerInterfaceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_be_constructed_with_custom_factory()
    {
        $mockContainer = Mockery::mock(Container::class);
        $mockFactory = Mockery::mock(BotClientFactoryInterface::class);
        
        $config = [
            'default' => 'test',
            'bots' => [
                'test' => [
                    'bot_id' => 'test-bot',
                    'domain_id' => 'test-domain'
                ]
            ]
        ];
        
        $manager = new LineWorksManager($mockContainer, $config, $mockFactory);
        
        $this->assertInstanceOf(LineWorksManager::class, $manager);
    }

    /** @test */
    public function it_uses_injected_factory_to_create_bot_client()
    {
        $mockContainer = Mockery::mock(Container::class);
        $mockLineWorksClient = Mockery::mock(LineWorksClient::class);
        $mockBotClient = Mockery::mock(BotClientInterface::class);
        $mockFactory = Mockery::mock(BotClientFactoryInterface::class);
        
        // Configure the factory to return our mock
        $mockFactory->shouldReceive('create')
            ->with(Mockery::type(LineWorksClient::class))
            ->once()
            ->andReturn($mockBotClient);
        
        $config = [
            'default' => 'test',
            'bots' => [
                'test' => [
                    'bot_id' => 'test-bot',
                    'domain_id' => 'test-domain',
                    'private_key' => 'test-key',
                    'client_id' => 'test-client',
                    'client_secret' => 'test-secret',
                    'service_account' => 'test@example.com',
                    'scope' => 'test-scope'
                ]
            ]
        ];
        
        $manager = new LineWorksManager($mockContainer, $config, $mockFactory);
        $result = $manager->botClient();
        
        $this->assertInstanceOf(BotClientInterface::class, $result);
        $this->assertSame($mockBotClient, $result);
    }

    /** @test */
    public function it_can_change_factory_at_runtime_for_testing()
    {
        $mockContainer = Mockery::mock(Container::class);
        $mockBotClient1 = Mockery::mock(BotClientInterface::class);
        $mockBotClient2 = Mockery::mock(BotClientInterface::class);
        
        $mockFactory1 = Mockery::mock(BotClientFactoryInterface::class);
        $mockFactory1->shouldReceive('create')->andReturn($mockBotClient1);
        
        $mockFactory2 = Mockery::mock(BotClientFactoryInterface::class);
        $mockFactory2->shouldReceive('create')->andReturn($mockBotClient2);
        
        $config = [
            'default' => 'test',
            'bots' => [
                'test' => [
                    'bot_id' => 'test-bot',
                    'domain_id' => 'test-domain',
                    'private_key' => 'test-key',
                    'client_id' => 'test-client',
                    'client_secret' => 'test-secret',
                    'service_account' => 'test@example.com',
                    'scope' => 'test-scope'
                ]
            ]
        ];
        
        $manager = new LineWorksManager($mockContainer, $config, $mockFactory1);
        
        // Test with first factory
        $result1 = $manager->botClient();
        $this->assertSame($mockBotClient1, $result1);
        
        // Change factory at runtime (useful for testing)
        $manager->setBotClientFactory($mockFactory2);
        $result2 = $manager->botClient();
        $this->assertSame($mockBotClient2, $result2);
    }

    /** @test */
    public function it_uses_default_factory_when_none_provided()
    {
        $mockContainer = Mockery::mock(Container::class);
        
        $config = [
            'default' => 'test',
            'bots' => [
                'test' => [
                    'bot_id' => 'test-bot',
                    'domain_id' => 'test-domain',
                    'private_key' => 'test-key',
                    'client_id' => 'test-client',
                    'client_secret' => 'test-secret',
                    'service_account' => 'test@example.com',
                    'scope' => 'test-scope'
                ]
            ]
        ];
        
        // Create manager without factory (should use default)
        $manager = new LineWorksManager($mockContainer, $config);
        
        // This should work with the default BotClientFactory
        $result = $manager->botClient();
        $this->assertInstanceOf(BotClientInterface::class, $result);
    }
}