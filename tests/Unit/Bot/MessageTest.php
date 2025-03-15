<?php

namespace Tests\Unit\Bot;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Bot\Message\MessageClient;
use Sumihiro\LineWorksClient\DTO\Bot\Message\MessageResponse;
use Sumihiro\LineWorksClient\LineWorksClient;

class MessageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_send_text_message()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('getDomainId')->andReturn('test-domain-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/users/user1@example.com/messages',
            [
                'content' => [
                    'type' => 'text',
                    'text' => 'Hello, world!',
                ],
                'botId' => 'test-bot-id',
                'accountId' => 'user1@example.com',
                'domainId' => 'test-domain-id',
            ]
        )->andReturn(['messageId' => 'test-message-id']);

        $messageClient = new MessageClient($mockClient);
        $response = $messageClient->sendText('user1@example.com', 'Hello, world!');

        $this->assertInstanceOf(MessageResponse::class, $response);
    }

    /** @test */
    public function it_can_send_custom_message()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        $mockClient->shouldReceive('getDomainId')->andReturn('test-domain-id');
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/users/user1@example.com/messages',
            [
                'content' => [
                    'type' => 'button_template',
                    'contentText' => 'Please select an option',
                    'actions' => [
                        [
                            'type' => 'message',
                            'label' => 'Yes',
                            'text' => 'Yes',
                        ],
                        [
                            'type' => 'message',
                            'label' => 'No',
                            'text' => 'No',
                        ],
                    ],
                ],
                'botId' => 'test-bot-id',
                'accountId' => 'user1@example.com',
                'domainId' => 'test-domain-id',
            ]
        )->andReturn(['messageId' => 'test-message-id']);

        $messageClient = new MessageClient($mockClient);
        $response = $messageClient->sendMessage('user1@example.com', [
            'content' => [
                'type' => 'button_template',
                'contentText' => 'Please select an option',
                'actions' => [
                    [
                        'type' => 'message',
                        'label' => 'Yes',
                        'text' => 'Yes',
                    ],
                    [
                        'type' => 'message',
                        'label' => 'No',
                        'text' => 'No',
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(MessageResponse::class, $response);
    }
} 