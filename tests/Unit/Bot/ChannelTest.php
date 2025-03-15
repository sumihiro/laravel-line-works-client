<?php

namespace Tests\Unit\Bot;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Bot\Channel\ChannelClient;
use Sumihiro\LineWorksClient\DTO\Bot\ChannelResponse;
use Sumihiro\LineWorksClient\DTO\Bot\MessageResponse;
use Sumihiro\LineWorksClient\LineWorksClient;

class ChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_create_a_channel()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('getDomainId')->andReturn('test-domain-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/channels',
            [
                'botId' => 'test-bot-id',
                'domainId' => 'test-domain-id',
                'accountIds' => ['user1@example.com', 'user2@example.com'],
                'title' => 'Test Channel',
            ]
        )->andReturn(['channelId' => 'test-channel-id']);

        $channelClient = new ChannelClient($mockClient);
        $response = $channelClient->create(['user1@example.com', 'user2@example.com'], 'Test Channel');

        $this->assertInstanceOf(ChannelResponse::class, $response);
        $this->assertEquals('test-channel-id', $response->getChannelId());
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_get_channel_info()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('get')->once()->with(
            'bots/test-bot-id/channels/test-channel-id'
        )->andReturn([
            'channelId' => 'test-channel-id',
            'name' => 'Test Channel',
            'type' => 'group',
        ]);

        $channelClient = new ChannelClient($mockClient);
        $response = $channelClient->info('test-channel-id');

        $this->assertInstanceOf(ChannelResponse::class, $response);
        $this->assertEquals('test-channel-id', $response->getChannelId());
        $this->assertEquals('Test Channel', $response->getName());
        $this->assertEquals('group', $response->getType());
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_leave_a_channel()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/channels/test-channel-id/leave'
        )->andReturn([]);

        $channelClient = new ChannelClient($mockClient);
        $result = $channelClient->leave('test-channel-id');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_channel_members()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('get')->once()->with(
            'bots/test-bot-id/channels/test-channel-id/members'
        )->andReturn([
            'channelId' => 'test-channel-id',
            'members' => [
                ['accountId' => 'user1@example.com'],
                ['accountId' => 'user2@example.com'],
            ],
            'memberCount' => 2,
        ]);

        $channelClient = new ChannelClient($mockClient);
        $response = $channelClient->members('test-channel-id');

        $this->assertInstanceOf(ChannelResponse::class, $response);
        $this->assertEquals('test-channel-id', $response->getChannelId());
        $this->assertCount(2, $response->getMembers());
        $this->assertEquals(2, $response->getMemberCount());
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_send_message_to_channel()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('getDomainId')->andReturn('test-domain-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/channels/test-channel-id/messages',
            [
                'content' => [
                    'type' => 'text',
                    'text' => 'Hello, channel!',
                ],
                'botId' => 'test-bot-id',
                'channelId' => 'test-channel-id',
                'domainId' => 'test-domain-id',
            ]
        )->andReturn(['messageId' => 'test-message-id']);

        $channelClient = new ChannelClient($mockClient);
        $response = $channelClient->sendMessage('test-channel-id', [
            'content' => [
                'type' => 'text',
                'text' => 'Hello, channel!',
            ],
        ]);

        $this->assertInstanceOf(MessageResponse::class, $response);
    }
} 