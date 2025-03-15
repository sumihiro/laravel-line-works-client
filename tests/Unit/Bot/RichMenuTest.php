<?php

namespace Tests\Unit\Bot;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Bot\Attachment\AttachmentClient;
use Sumihiro\LineWorksClient\Bot\RichMenu\RichMenuClient;
use Sumihiro\LineWorksClient\DTO\Bot\Attachment\CreateResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\CreateResponse as RichMenuCreateResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DefaultMenuResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DeleteResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DetailResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\ListResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\SetImageResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\UserMenuResponse;
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
            'bots/test-bot-id/richmenus'
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

        $this->assertInstanceOf(ListResponse::class, $response);
        $this->assertTrue($response->hasRichMenus());
        $this->assertEquals(1, $response->count());
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
            'richmenuName' => 'Test Rich Menu',
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
            'bots/test-bot-id/richmenus',
            $richMenuData
        )->andReturn(['richmenuId' => 'test-rich-menu-id']);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->create($richMenuData);

        $this->assertInstanceOf(RichMenuCreateResponse::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('test-rich-menu-id', $response->getRichMenuId());
    }

    /** @test */
    public function it_can_delete_rich_menu()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('delete')->once()->with(
            'bots/test-bot-id/richmenus/test-rich-menu-id'
        )->andReturn([]);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->delete('test-rich-menu-id');

        $this->assertInstanceOf(DeleteResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_set_rich_menu_for_user()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/users/user1@example.com/richmenus',
            ['richMenuId' => 'test-rich-menu-id']
        )->andReturn([]);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->setForUser('user1@example.com', 'test-rich-menu-id');

        $this->assertInstanceOf(UserMenuResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_get_rich_menu_for_user()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('get')->once()->with(
            'bots/test-bot-id/users/user1@example.com/richmenus'
        )->andReturn(['richMenuId' => 'test-rich-menu-id']);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->getForUser('user1@example.com');

        $this->assertInstanceOf(UserMenuResponse::class, $response);
        $this->assertEquals('test-rich-menu-id', $response->getRichMenuId());
    }

    /** @test */
    public function it_can_delete_rich_menu_for_user()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('delete')->once()->with(
            'bots/test-bot-id/users/user1@example.com/richmenus'
        )->andReturn([]);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->deleteForUser('user1@example.com');

        $this->assertInstanceOf(UserMenuResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_set_rich_menu_image()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        
        // Mock setting rich menu image
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/richmenus/test-rich-menu-id/content',
            ['fileId' => 'test-file-id']
        )->andReturn([]);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->setRichMenuImage('test-rich-menu-id', 'test-file-id');

        $this->assertInstanceOf(SetImageResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_upload_rich_menu_image()
    {
        // Create a temporary test file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_image_');
        file_put_contents($tempFile, 'test image content');

        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        
        // Mock AttachmentClient
        $mockAttachmentClient = Mockery::mock(AttachmentClient::class);
        $mockAttachmentResponse = Mockery::mock(CreateResponse::class);
        
        // Mock UploadClient
        $mockUploadClient = Mockery::mock(\Sumihiro\LineWorksClient\Upload\UploadClient::class);
        
        // Mock BotClient
        $mockBotClient = Mockery::mock(\Sumihiro\LineWorksClient\Bot\BotClient::class);
        
        // Mock AttachmentResponse methods
        $mockAttachmentResponse->shouldReceive('getUploadUrl')->andReturn('https://example.com/upload');
        $mockAttachmentResponse->shouldReceive('getFileId')->andReturn('test-file-id');
        
        // Mock AttachmentClient methods
        $mockAttachmentClient->shouldReceive('create')
            ->once()
            ->with($tempFile, Mockery::any())
            ->andReturn($mockAttachmentResponse);
            
        // Mock BotClient methods
        $mockBotClient->shouldReceive('attachment')
            ->once()
            ->andReturn($mockAttachmentClient);
            
        // Mock LineWorksClient bot method
        $mockClient->shouldReceive('bot')
            ->once()
            ->andReturn($mockBotClient);
            
        // Mock UploadClient methods
        $mockUploadClient->shouldReceive('upload')
            ->once()
            ->with('https://example.com/upload', $tempFile, Mockery::any())
            ->andReturn(true);
            
        // Mock LineWorksClient upload method
        $mockClient->shouldReceive('upload')
            ->once()
            ->andReturn($mockUploadClient);
            
        // Mock setting rich menu image
        $mockClient->shouldReceive('post')
            ->once()
            ->with('bots/test-bot-id/richmenus/test-rich-menu-id/content', ['fileId' => 'test-file-id'])
            ->andReturn([]);
            
        // Create RichMenuClient instance
        $richMenuClient = new RichMenuClient($mockClient);

        $response = $richMenuClient->uploadImage('test-rich-menu-id', $tempFile);

        $this->assertInstanceOf(SetImageResponse::class, $response);
        $this->assertTrue($response->isSuccess());

        // Clean up the temporary file
        unlink($tempFile);
    }

    /** @test */
    public function it_can_set_default_rich_menu()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/richmenus/default',
            ['richMenuId' => 'test-rich-menu-id']
        )->andReturn([]);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->setDefault('test-rich-menu-id');

        $this->assertInstanceOf(DefaultMenuResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_get_default_rich_menu()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('get')->once()->with(
            'bots/test-bot-id/richmenus/default'
        )->andReturn(['richMenuId' => 'test-rich-menu-id']);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->getDefault();

        $this->assertInstanceOf(DefaultMenuResponse::class, $response);
        $this->assertEquals('test-rich-menu-id', $response->getRichMenuId());
    }

    /** @test */
    public function it_can_delete_default_rich_menu()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('delete')->once()->with(
            'bots/test-bot-id/richmenus/default'
        )->andReturn([]);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->deleteDefault();

        $this->assertInstanceOf(DefaultMenuResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    /** @test */
    public function it_can_get_rich_menu()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('get')->once()->with(
            'bots/test-bot-id/richmenus/test-rich-menu-id'
        )->andReturn(['richmenuId' => 'test-rich-menu-id', 'richmenuName' => 'Test Rich Menu']);

        $richMenuClient = new RichMenuClient($mockClient);
        $response = $richMenuClient->get('test-rich-menu-id');

        $this->assertInstanceOf(DetailResponse::class, $response);
        $this->assertEquals('test-rich-menu-id', $response->getRichMenuId());
        $this->assertEquals('Test Rich Menu', $response->getName());
    }
} 