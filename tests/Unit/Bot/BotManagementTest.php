<?php

namespace Tests\Unit\Bot;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Bot\Management\BotManagementClient;
use Sumihiro\LineWorksClient\DTO\Bot\BotInfoResponse;
use Sumihiro\LineWorksClient\LineWorksClient;

class BotManagementTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_get_bot_info()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('get')->once()->with(
            'bots/test-bot-id'
        )->andReturn([
            'botId' => 'test-bot-id',
            'name' => 'Test Bot',
            'status' => 'active',
            'photoUrl' => 'https://example.com/bot.png',
            'description' => 'This is a test bot',
        ]);

        $botManagementClient = new BotManagementClient($mockClient);
        $response = $botManagementClient->info();

        $this->assertInstanceOf(BotInfoResponse::class, $response);
        $this->assertEquals('test-bot-id', $response->getBotId());
        $this->assertEquals('Test Bot', $response->getName());
        $this->assertEquals('active', $response->getStatus());
        $this->assertEquals('https://example.com/bot.png', $response->getPhotoUrl());
        $this->assertEquals('This is a test bot', $response->getDescription());
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_get_domain_info()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('get')->once()->with(
            'bots/test-bot-id/domain'
        )->andReturn([
            'domainId' => 'test-domain-id',
            'domainName' => 'Test Domain',
        ]);

        $botManagementClient = new BotManagementClient($mockClient);
        $response = $botManagementClient->domainInfo();

        $this->assertIsArray($response);
        $this->assertEquals('test-domain-id', $response['domainId']);
        $this->assertEquals('Test Domain', $response['domainName']);
    }
} 