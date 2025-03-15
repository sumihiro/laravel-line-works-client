<?php

namespace Tests\Unit\Bot;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Bot\Channel\ChannelClient;
use Sumihiro\LineWorksClient\DTO\Bot\Channel\CreateChannelResponse;
use Sumihiro\LineWorksClient\DTO\Bot\Channel\InfoResponse;
use Sumihiro\LineWorksClient\DTO\Bot\Channel\MembersResponse;
use Sumihiro\LineWorksClient\DTO\Bot\Message\MessageResponse;
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
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/channels',
            [
                'members' => ['user1@example.com', 'user2@example.com'],
                'title' => 'Test Channel',
            ]
        )->andReturn(['channelId' => 'test-channel-id']);

        $channelClient = new ChannelClient($mockClient);
        $response = $channelClient->create(['user1@example.com', 'user2@example.com'], 'Test Channel');

        $this->assertInstanceOf(CreateChannelResponse::class, $response);
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
            'domainId' => 123456,
            'channelId' => 'test-channel-id',
            'title' => 'Test Channel',
            'channelType' => [
                'type' => 'group'
            ],
            'createdTime' => 1609459200000,
            'status' => 'active'
        ]);

        $channelClient = new ChannelClient($mockClient);
        $response = $channelClient->info('test-channel-id');

        $this->assertInstanceOf(InfoResponse::class, $response);
        $this->assertEquals('test-channel-id', $response->getChannelId());
        $this->assertEquals('Test Channel', $response->getTitle());
        $this->assertEquals('group', $response->getType());
        $this->assertEquals(123456, $response->getDomainId());
        $this->assertEquals(1609459200000, $response->getCreatedTime());
        $this->assertEquals('active', $response->getStatus());
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
            'members' => [
                'user1@example.com',
                'user2@example.com',
            ],
            'responseMetaData' => [
                'nextCursor' => 'next-page-token'
            ]
        ]);

        $channelClient = new ChannelClient($mockClient);
        $response = $channelClient->members('test-channel-id');

        $this->assertInstanceOf(MembersResponse::class, $response);
        $this->assertCount(2, $response->getMembers());
        $this->assertEquals(2, $response->getMemberCount());
        $this->assertEquals('user1@example.com', $response->getMembers()[0]['accountId']);
        $this->assertEquals('user2@example.com', $response->getMembers()[1]['accountId']);
        $this->assertEquals('next-page-token', $response->getNextCursor());
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_send_message_to_channel()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/channels/test-channel-id/messages',
            [
                'content' => [
                    'type' => 'text',
                    'text' => 'Hello, channel!',
                ],
            ]
        )->andReturn([]);

        $channelClient = new ChannelClient($mockClient);
        $response = $channelClient->sendMessage('test-channel-id', [
            'content' => [
                'type' => 'text',
                'text' => 'Hello, channel!',
            ],
        ]);

        $this->assertInstanceOf(MessageResponse::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->getMessageId());
    }
} 