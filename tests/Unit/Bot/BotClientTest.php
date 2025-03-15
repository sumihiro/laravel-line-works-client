<?php

namespace Tests\Unit\Bot;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Bot\BotClient;
use Sumihiro\LineWorksClient\Bot\Channel\ChannelClient;
use Sumihiro\LineWorksClient\Bot\Management\BotManagementClient;
use Sumihiro\LineWorksClient\Bot\Message\MessageClient;
use Sumihiro\LineWorksClient\Bot\RichMenu\RichMenuClient;
use Sumihiro\LineWorksClient\LineWorksClient;

class BotClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_get_channel_client()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $botClient = new BotClient($mockClient);
        
        $channelClient = $botClient->channel();
        
        $this->assertInstanceOf(ChannelClient::class, $channelClient);
    }

    /** @test */
    public function it_can_get_message_client()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $botClient = new BotClient($mockClient);
        
        $messageClient = $botClient->message();
        
        $this->assertInstanceOf(MessageClient::class, $messageClient);
    }

    /** @test */
    public function it_can_get_rich_menu_client()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $botClient = new BotClient($mockClient);
        
        $richMenuClient = $botClient->richMenu();
        
        $this->assertInstanceOf(RichMenuClient::class, $richMenuClient);
    }

    /** @test */
    public function it_can_get_bot_management_client()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $botClient = new BotClient($mockClient);
        
        $botManagementClient = $botClient->management();
        
        $this->assertInstanceOf(BotManagementClient::class, $botManagementClient);
    }

    /** @test */
    public function it_can_get_line_works_client()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $botClient = new BotClient($mockClient);
        
        $client = $botClient->getClient();
        
        $this->assertSame($mockClient, $client);
    }

    /** @test */
    public function it_reuses_the_same_client_instance()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $botClient = new BotClient($mockClient);
        
        $channelClient1 = $botClient->channel();
        $channelClient2 = $botClient->channel();
        
        $this->assertSame($channelClient1, $channelClient2);
        
        $messageClient1 = $botClient->message();
        $messageClient2 = $botClient->message();
        
        $this->assertSame($messageClient1, $messageClient2);
        
        $richMenuClient1 = $botClient->richMenu();
        $richMenuClient2 = $botClient->richMenu();
        
        $this->assertSame($richMenuClient1, $richMenuClient2);
        
        $botManagementClient1 = $botClient->management();
        $botManagementClient2 = $botClient->management();
        
        $this->assertSame($botManagementClient1, $botManagementClient2);
    }
} 