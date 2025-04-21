<?php

namespace Tests\Unit\Facades;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Facades\LineWorks;
use Sumihiro\LineWorksClient\LineWorksManager;
use Sumihiro\LineWorksClient\LineWorksClient;
use Sumihiro\LineWorksClient\Bot\BotClient;

class LineWorksTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Facadeのルートを設定
        $mockManager = Mockery::mock(LineWorksManager::class);
        LineWorks::swap($mockManager);
    }

    /** @test */
    public function it_can_get_bot_client()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockManager = Mockery::mock(LineWorksManager::class);
        $mockManager->shouldReceive('bot')->once()->andReturn($mockClient);

        LineWorks::swap($mockManager);

        $this->assertInstanceOf(LineWorksClient::class, LineWorks::bot());
    }

    /** @test */
    public function it_can_get_bot_client_with_name()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockManager = Mockery::mock(LineWorksManager::class);
        $mockManager->shouldReceive('bot')->once()->with('test-bot')->andReturn($mockClient);

        LineWorks::swap($mockManager);

        $this->assertInstanceOf(LineWorksClient::class, LineWorks::bot('test-bot'));
    }

    /** @test */
    public function it_can_get_bot_client_client()
    {
        $mockBotClient = Mockery::mock(BotClient::class);
        $mockManager = Mockery::mock(LineWorksManager::class);
        $mockManager->shouldReceive('botClient')->once()->andReturn($mockBotClient);

        LineWorks::swap($mockManager);

        $this->assertInstanceOf(BotClient::class, LineWorks::botClient());
    }

    /** @test */
    public function it_can_get_default_bot()
    {
        $mockManager = Mockery::mock(LineWorksManager::class);
        $mockManager->shouldReceive('getDefaultBot')->once()->andReturn('default-bot');

        LineWorks::swap($mockManager);

        $this->assertEquals('default-bot', LineWorks::getDefaultBot());
    }

    /** @test */
    public function it_can_make_get_request()
    {
        $mockManager = Mockery::mock(LineWorksManager::class);
        $mockManager->shouldAllowMockingProtectedMethods();
        $mockManager->shouldReceive('get')->once()->with(
            'test-endpoint',
            ['param' => 'value'],
            ['header' => 'value']
        )->andReturn(['response' => 'data']);

        LineWorks::swap($mockManager);

        $response = LineWorks::get('test-endpoint', ['param' => 'value'], ['header' => 'value']);

        $this->assertEquals(['response' => 'data'], $response);
    }

    /** @test */
    public function it_can_make_post_request()
    {
        $mockManager = Mockery::mock(LineWorksManager::class);
        $mockManager->shouldAllowMockingProtectedMethods();
        $mockManager->shouldReceive('post')->once()->with(
            'test-endpoint',
            ['data' => 'value'],
            ['header' => 'value']
        )->andReturn(['response' => 'data']);

        LineWorks::swap($mockManager);

        $response = LineWorks::post('test-endpoint', ['data' => 'value'], ['header' => 'value']);

        $this->assertEquals(['response' => 'data'], $response);
    }

    /** @test */
    public function it_can_make_put_request()
    {
        $mockManager = Mockery::mock(LineWorksManager::class);
        $mockManager->shouldAllowMockingProtectedMethods();
        $mockManager->shouldReceive('put')->once()->with(
            'test-endpoint',
            ['data' => 'value'],
            ['header' => 'value']
        )->andReturn(['response' => 'data']);

        LineWorks::swap($mockManager);

        $response = LineWorks::put('test-endpoint', ['data' => 'value'], ['header' => 'value']);

        $this->assertEquals(['response' => 'data'], $response);
    }

    /** @test */
    public function it_can_make_delete_request()
    {
        $mockManager = Mockery::mock(LineWorksManager::class);
        $mockManager->shouldAllowMockingProtectedMethods();
        $mockManager->shouldReceive('delete')->once()->with(
            'test-endpoint',
            ['param' => 'value'],
            ['header' => 'value']
        )->andReturn(['response' => 'data']);

        LineWorks::swap($mockManager);

        $response = LineWorks::delete('test-endpoint', ['param' => 'value'], ['header' => 'value']);

        $this->assertEquals(['response' => 'data'], $response);
    }
} 