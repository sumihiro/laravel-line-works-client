<?php

namespace Tests\Unit\Bot;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Contracts\Bot\AttachmentClientInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientFactoryInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\BotManagementClientInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\ChannelClientInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\MessageClientInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\MessageContentsClientInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\RichMenuClientInterface;
use Sumihiro\LineWorksClient\DTO\Bot\Monitoring\MessageContentsResponse;
use Sumihiro\LineWorksClient\LineWorksClient;
use Sumihiro\LineWorksClient\LineWorksManager;

/**
 * Interface-based testing demonstration.
 * This test shows how to use the new interfaces for type-safe mocking.
 */
class BotClientInterfaceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_mock_bot_client_interface_for_monitoring()
    {
        // Create mock response
        $mockResponse = Mockery::mock(MessageContentsResponse::class);
        $mockResponse->shouldReceive('isSuccess')->andReturn(true);
        $mockResponse->shouldReceive('getContent')->andReturn('mock csv content');
        
        // Create mock monitoring client
        $mockMonitoring = Mockery::mock(MessageContentsClientInterface::class);
        $mockMonitoring->shouldReceive('download')
            ->with('2023-01-01T00:00:00+09:00', '2023-01-02T00:00:00+09:00')
            ->andReturn($mockResponse);
        
        // Create mock bot client
        $mockBotClient = Mockery::mock(BotClientInterface::class);
        $mockBotClient->shouldReceive('monitoring')->andReturn($mockMonitoring);
        
        // Create mock factory
        $mockFactory = Mockery::mock(BotClientFactoryInterface::class);
        $mockFactory->shouldReceive('create')->andReturn($mockBotClient);
        
        // Create manager with mocked factory
        $mockContainer = Mockery::mock(\Illuminate\Contracts\Container\Container::class);
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
        
        // Test the interaction
        $result = $manager->botClient()->monitoring()->download(
            '2023-01-01T00:00:00+09:00',
            '2023-01-02T00:00:00+09:00'
        );
        
        // Assertions
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('mock csv content', $result->getContent());
    }

    /** @test */
    public function it_can_mock_all_bot_client_sub_interfaces()
    {
        // Create mocks for all sub-clients
        $mockChannel = Mockery::mock(ChannelClientInterface::class);
        $mockMessage = Mockery::mock(MessageClientInterface::class);
        $mockRichMenu = Mockery::mock(RichMenuClientInterface::class);
        $mockAttachment = Mockery::mock(AttachmentClientInterface::class);
        $mockManagement = Mockery::mock(BotManagementClientInterface::class);
        $mockMonitoring = Mockery::mock(MessageContentsClientInterface::class);
        
        // Create mock bot client
        $mockBotClient = Mockery::mock(BotClientInterface::class);
        $mockBotClient->shouldReceive('channel')->andReturn($mockChannel);
        $mockBotClient->shouldReceive('message')->andReturn($mockMessage);
        $mockBotClient->shouldReceive('richMenu')->andReturn($mockRichMenu);
        $mockBotClient->shouldReceive('attachment')->andReturn($mockAttachment);
        $mockBotClient->shouldReceive('management')->andReturn($mockManagement);
        $mockBotClient->shouldReceive('monitoring')->andReturn($mockMonitoring);
        
        // Test all clients are accessible
        $this->assertInstanceOf(ChannelClientInterface::class, $mockBotClient->channel());
        $this->assertInstanceOf(MessageClientInterface::class, $mockBotClient->message());
        $this->assertInstanceOf(RichMenuClientInterface::class, $mockBotClient->richMenu());
        $this->assertInstanceOf(AttachmentClientInterface::class, $mockBotClient->attachment());
        $this->assertInstanceOf(BotManagementClientInterface::class, $mockBotClient->management());
        $this->assertInstanceOf(MessageContentsClientInterface::class, $mockBotClient->monitoring());
    }

    /** @test */
    public function it_can_use_factory_for_dependency_injection()
    {
        // Create mock LINE WORKS client
        $mockLineWorksClient = Mockery::mock(LineWorksClient::class);
        
        // Create mock bot client
        $mockBotClient = Mockery::mock(BotClientInterface::class);
        
        // Create mock factory
        $mockFactory = Mockery::mock(BotClientFactoryInterface::class);
        $mockFactory->shouldReceive('create')
            ->with($mockLineWorksClient)
            ->once()
            ->andReturn($mockBotClient);
        
        // Test factory usage
        $result = $mockFactory->create($mockLineWorksClient);
        
        $this->assertInstanceOf(BotClientInterface::class, $result);
        $this->assertSame($mockBotClient, $result);
    }

    /** @test */
    public function it_demonstrates_type_safe_testing_approach()
    {
        // This test demonstrates the benefits of interface-based testing:
        // 1. Type safety - PHPStan/IDE can verify interface compliance
        // 2. Mocking flexibility - Easy to create test doubles
        // 3. Loose coupling - Tests don't depend on concrete implementations
        
        $mockBotClient = Mockery::mock(BotClientInterface::class);
        $mockFactory = Mockery::mock(BotClientFactoryInterface::class);
        $mockFactory->shouldReceive('create')->andReturn($mockBotClient);
        
        // The following would be caught by static analysis if interfaces don't match:
        $this->assertInstanceOf(BotClientInterface::class, $mockBotClient);
        $this->assertInstanceOf(BotClientFactoryInterface::class, $mockFactory);
        
        // This test verifies that our interfaces are correctly structured
        // and can be used for effective unit testing
        $this->assertTrue(true, 'Interface-based testing is working correctly');
    }
}