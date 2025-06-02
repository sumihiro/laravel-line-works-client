<?php

namespace Sumihiro\LineWorksClient\Tests\Unit\Bot\Monitoring;

use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\DTO\Bot\Monitoring\MessageContentsResponse;

/**
 * @requires PHP >= 8.1
 */
class MessageContentsResponseTest extends TestCase
{
    /** @test */
    public function it_can_parse_basic_csv_content(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Hello World\n2025-05-30T18:25:00+09:00,user@example.com,[Bot]test,channel123,Hi there";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertCount(2, $messages);
        
        $this->assertEquals('2025-05-30T18:24:53+09:00', $messages[0]['datetime']);
        $this->assertEquals('[Bot]test', $messages[0]['sender']);
        $this->assertEquals('user@example.com', $messages[0]['receiver']);
        $this->assertEquals('channel123', $messages[0]['channel_id']);
        $this->assertEquals('Hello World', $messages[0]['message']);
        
        $this->assertEquals('2025-05-30T18:25:00+09:00', $messages[1]['datetime']);
        $this->assertEquals('user@example.com', $messages[1]['sender']);
        $this->assertEquals('[Bot]test', $messages[1]['receiver']);
        $this->assertEquals('channel123', $messages[1]['channel_id']);
        $this->assertEquals('Hi there', $messages[1]['message']);
    }

    /** @test */
    public function it_can_parse_csv_with_line_breaks_in_fields(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,\"Hello\nWorld\nMultiline\"\n2025-05-30T18:25:00+09:00,user@example.com,[Bot]test,channel123,\"Line 1\nLine 2\"";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertCount(2, $messages);
        $this->assertEquals("Hello\nWorld\nMultiline", $messages[0]['message']);
        $this->assertEquals("Line 1\nLine 2", $messages[1]['message']);
    }

    /** @test */
    public function it_can_parse_csv_with_escaped_quotes(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,\"He said \"\"Hello\"\" to me\"\n2025-05-30T18:25:00+09:00,user@example.com,[Bot]test,channel123,\"Quote: \"\"Test\"\"\"";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertCount(2, $messages);
        $this->assertEquals('He said "Hello" to me', $messages[0]['message']);
        $this->assertEquals('Quote: "Test"', $messages[1]['message']);
    }

    /** @test */
    public function it_can_parse_csv_with_commas_in_fields(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,\"Hello, World, How are you?\"\n2025-05-30T18:25:00+09:00,user@example.com,[Bot]test,channel123,\"One, Two, Three\"";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertCount(2, $messages);
        $this->assertEquals('Hello, World, How are you?', $messages[0]['message']);
        $this->assertEquals('One, Two, Three', $messages[1]['message']);
    }

    /** @test */
    public function it_handles_empty_csv_content(): void
    {
        // Arrange
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => '']
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertEmpty($messages);
        $this->assertEquals(0, $response->getMessageCount());
    }

    /** @test */
    public function it_handles_csv_with_header_only(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertEmpty($messages);
        $this->assertEquals(0, $response->getMessageCount());
    }

    /** @test */
    public function it_handles_csv_with_empty_rows(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Hello\n\n2025-05-30T18:25:00+09:00,user@example.com,[Bot]test,channel123,Hi\n\n";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertCount(2, $messages);
        $this->assertEquals('Hello', $messages[0]['message']);
        $this->assertEquals('Hi', $messages[1]['message']);
    }

    /** @test */
    public function it_maps_japanese_headers_to_english_keys(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Hello";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertCount(1, $messages);
        $this->assertArrayHasKey('datetime', $messages[0]);
        $this->assertArrayHasKey('sender', $messages[0]);
        $this->assertArrayHasKey('receiver', $messages[0]);
        $this->assertArrayHasKey('channel_id', $messages[0]);
        $this->assertArrayHasKey('message', $messages[0]);
    }

    /** @test */
    public function it_preserves_unknown_headers(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,UnknownColumn,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,unknown_value,Hello";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertCount(1, $messages);
        $this->assertArrayHasKey('UnknownColumn', $messages[0]);
        $this->assertEquals('unknown_value', $messages[0]['UnknownColumn']);
    }

    /** @test */
    public function it_can_filter_messages_by_sender(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Hello\n2025-05-30T18:25:00+09:00,user@example.com,[Bot]test,channel123,Hi\n2025-05-30T18:26:00+09:00,[Bot]test,user2@example.com,channel123,Another message";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $botMessages = $response->getMessagesBySender('[Bot]test');
        $userMessages = $response->getMessagesBySender('user@example.com');

        // Assert
        $this->assertCount(2, $botMessages);
        $this->assertCount(1, $userMessages);
    }

    /** @test */
    public function it_can_filter_messages_by_channel(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Hello\n2025-05-30T18:25:00+09:00,user@example.com,[Bot]test,channel456,Hi\n2025-05-30T18:26:00+09:00,[Bot]test,user2@example.com,channel123,Another message";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $channel123Messages = $response->getMessagesByChannel('channel123');
        $channel456Messages = $response->getMessagesByChannel('channel456');

        // Assert
        $this->assertCount(2, $channel123Messages);
        $this->assertCount(1, $channel456Messages);
    }

    /** @test */
    public function it_can_filter_messages_by_date_range(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-29T18:24:53+09:00,[Bot]test,user@example.com,channel123,Old message\n2025-05-30T18:25:00+09:00,user@example.com,[Bot]test,channel123,Current message\n2025-05-31T18:26:00+09:00,[Bot]test,user2@example.com,channel123,Future message";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $messages = $response->getMessagesByDateRange('2025-05-30', '2025-05-30');

        // Assert
        $this->assertCount(1, $messages);
        $this->assertEquals('Current message', array_values($messages)[0]['message']);
    }

    /** @test */
    public function it_can_separate_bot_and_user_messages(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Bot message 1\n2025-05-30T18:25:00+09:00,user@example.com,[Bot]test,channel123,User message\n2025-05-30T18:26:00+09:00,[Bot]another,user2@example.com,channel123,Bot message 2";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent]
        ]);

        // Act
        $botMessages = $response->getBotMessages();
        $userMessages = $response->getUserMessages();

        // Assert
        $this->assertCount(2, $botMessages);
        $this->assertCount(1, $userMessages);
        
        // Verify bot messages start with [Bot]
        foreach ($botMessages as $message) {
            $this->assertStringStartsWith('[Bot]', $message['sender']);
        }
        
        // Verify user messages don't start with [Bot]
        foreach ($userMessages as $message) {
            $this->assertStringStartsNotWith('[Bot]', $message['sender']);
        }
    }

    /** @test */
    public function it_provides_correct_success_status(): void
    {
        // Arrange - Success case
        $successResponse = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => 'some,csv,content']
        ]);

        // Arrange - Failure case
        $failureResponse = new MessageContentsResponse([]);

        // Act & Assert
        $this->assertTrue($successResponse->isSuccess());
        $this->assertFalse($failureResponse->isSuccess());
    }

    /** @test */
    public function it_converts_to_array_correctly(): void
    {
        // Arrange
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Hello";
        $metadata = ['some' => 'metadata'];
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => ['raw_response' => $csvContent],
            'metadata' => $metadata
        ]);

        // Act
        $array = $response->toArray();

        // Assert
        $this->assertArrayHasKey('downloadUrl', $array);
        $this->assertArrayHasKey('messageCount', $array);
        $this->assertArrayHasKey('messages', $array);
        $this->assertArrayHasKey('metadata', $array);
        
        $this->assertEquals('https://example.com/download', $array['downloadUrl']);
        $this->assertEquals(1, $array['messageCount']);
        $this->assertCount(1, $array['messages']);
        $this->assertEquals($metadata, $array['metadata']);
    }

    /** @test */
    public function it_handles_csv_content_from_string_format(): void
    {
        // Arrange - Test when content is directly a string instead of array with raw_response
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Hello";
        
        $response = new MessageContentsResponse([
            'downloadUrl' => 'https://example.com/download',
            'content' => $csvContent
        ]);

        // Act
        $messages = $response->getMessages();

        // Assert
        $this->assertCount(1, $messages);
        $this->assertEquals('Hello', $messages[0]['message']);
    }
} 