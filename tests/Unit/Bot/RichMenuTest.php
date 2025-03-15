<?php

namespace Tests\Unit\Bot;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Bot\RichMenu\RichMenuClient;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenuResponse;
use Sumihiro\LineWorksClient\LineWorksClient;

class RichMenuTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_get_rich_menu_list()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('get')->once()->with(
            'bots/test-bot-id/richmenu'
        )->andReturn([
            'richmenus' => [
                [
                    'richMenuId' => 'test-rich-menu-id',
                    'name' => 'Test Rich Menu',
                ],
            ],
        ]);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->list();

        $this->assertInstanceOf(RichMenuResponse::class, $response);
    }

    /** @test */
    public function it_can_create_rich_menu()
    {
        $richMenuData = [
            'size' => [
                'width' => 2500,
                'height' => 1686,
            ],
            'selected' => false,
            'name' => 'Test Rich Menu',
            'chatBarText' => 'Menu',
            'areas' => [
                [
                    'bounds' => [
                        'x' => 0,
                        'y' => 0,
                        'width' => 1250,
                        'height' => 1686,
                    ],
                    'action' => [
                        'type' => 'message',
                        'text' => 'Option 1',
                    ],
                ],
                [
                    'bounds' => [
                        'x' => 1250,
                        'y' => 0,
                        'width' => 1250,
                        'height' => 1686,
                    ],
                    'action' => [
                        'type' => 'message',
                        'text' => 'Option 2',
                    ],
                ],
            ],
        ];

        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/richmenu',
            $richMenuData
        )->andReturn(['richMenuId' => 'test-rich-menu-id']);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->create($richMenuData);

        $this->assertInstanceOf(RichMenuResponse::class, $response);
    }

    /** @test */
    public function it_can_delete_rich_menu()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('delete')->once()->with(
            'bots/test-bot-id/richmenu/test-rich-menu-id'
        )->andReturn([]);

        $richMenuClient = new RichMenuClient($mockClient);
        $result = $richMenuClient->delete('test-rich-menu-id');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_set_rich_menu_for_user()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/users/user1@example.com/richmenu',
            ['richMenuId' => 'test-rich-menu-id']
        )->andReturn([]);

        $richMenuClient = new RichMenuClient($mockClient);
        $result = $richMenuClient->setForUser('user1@example.com', 'test-rich-menu-id');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_rich_menu_for_user()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('get')->once()->with(
            'bots/test-bot-id/users/user1@example.com/richmenu'
        )->andReturn(['richMenuId' => 'test-rich-menu-id']);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->getForUser('user1@example.com');

        $this->assertInstanceOf(RichMenuResponse::class, $response);
    }

    /** @test */
    public function it_can_delete_rich_menu_for_user()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('delete')->once()->with(
            'bots/test-bot-id/users/user1@example.com/richmenu'
        )->andReturn([]);

        $richMenuClient = new RichMenuClient($mockClient);
        $result = $richMenuClient->deleteForUser('user1@example.com');

        $this->assertTrue($result);
    }
} 