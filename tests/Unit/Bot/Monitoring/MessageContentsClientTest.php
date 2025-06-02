<?php

namespace Sumihiro\LineWorksClient\Tests\Unit\Bot\Monitoring;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Sumihiro\LineWorksClient\Auth\AccessTokenManager;
use Sumihiro\LineWorksClient\Bot\Monitoring\MessageContentsClient;
use Sumihiro\LineWorksClient\DTO\Bot\Monitoring\MessageContentsResponse;
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\LineWorksClient;

/**
 * @requires PHP >= 8.1
 * @requires Mockery
 */
class MessageContentsClientTest extends TestCase
{
    protected MessageContentsClient $client;
    /** @var LineWorksClient&MockInterface */
    protected LineWorksClient $lineWorksClient;
    /** @var AccessTokenManager&MockInterface */
    protected AccessTokenManager $tokenManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        /** @var LineWorksClient&MockInterface $lineWorksClient */
        $lineWorksClient = Mockery::mock(LineWorksClient::class);
        $this->lineWorksClient = $lineWorksClient;
        
        /** @var AccessTokenManager&MockInterface $tokenManager */
        $tokenManager = Mockery::mock(AccessTokenManager::class);
        $this->tokenManager = $tokenManager;
        
        $this->client = new MessageContentsClient($this->lineWorksClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_download_message_contents_successfully(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';
        $language = 'ja_JP';
        $downloadUrl = 'https://example.com/download/abc123';
        $csvContent = "日時,送信者,受信者,チャンネルID,トーク\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Hello World\n";

        // Mock the 302 redirect response
        $redirectResponse = Mockery::mock(ResponseInterface::class);
        $redirectResponse->shouldReceive('getStatusCode')->andReturn(302);
        $redirectResponse->shouldReceive('getHeader')->with('Location')->andReturn([$downloadUrl]);

        // Mock the download response
        $downloadResponse = ['raw_response' => $csvContent];

        $this->lineWorksClient->shouldReceive('get')
            ->with('monitoring/message-contents/download', [
                'startTime' => $startTime,
                'endTime' => $endTime,
                'language' => $language,
            ], [], ['allow_redirects' => false])
            ->andReturn($redirectResponse);

        $this->lineWorksClient->shouldReceive('getAccessTokenManager')
            ->andReturn($this->tokenManager);

        $this->tokenManager->shouldReceive('getToken')
            ->andReturn('test-access-token');

        $this->lineWorksClient->shouldReceive('requestExternal')
            ->with('GET', $downloadUrl, [
                'headers' => [
                    'Authorization' => 'Bearer test-access-token',
                ],
            ])
            ->andReturn($downloadResponse);

        // Act
        $response = $this->client->download($startTime, $endTime, $language);

        // Assert
        $this->assertInstanceOf(MessageContentsResponse::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals($downloadUrl, $response->getDownloadUrl());
        $this->assertEquals($csvContent, $response->getCsvContent());
        $this->assertEquals(1, $response->getMessageCount());
    }

    /** @test */
    public function it_can_get_download_url_only(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';
        $language = 'ja_JP';
        $downloadUrl = 'https://example.com/download/abc123';

        $redirectResponse = Mockery::mock(ResponseInterface::class);
        $redirectResponse->shouldReceive('getStatusCode')->andReturn(302);
        $redirectResponse->shouldReceive('getHeader')->with('Location')->andReturn([$downloadUrl]);

        $this->lineWorksClient->shouldReceive('get')
            ->with('monitoring/message-contents/download', [
                'startTime' => $startTime,
                'endTime' => $endTime,
                'language' => $language,
            ], [], ['allow_redirects' => false])
            ->andReturn($redirectResponse);

        // Act
        $result = $this->client->getDownloadUrlOnly($startTime, $endTime, $language);

        // Assert
        $this->assertEquals($downloadUrl, $result);
    }

    /** @test */
    public function it_validates_language_parameter(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';
        $invalidLanguage = 'invalid_lang';

        // Assert
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage("Invalid language 'invalid_lang'. Allowed values: ja_JP, ko_KR, zh_CN, zh_TW, en_US");

        // Act
        $this->client->getDownloadUrlOnly($startTime, $endTime, $invalidLanguage);
    }

    /** @test */
    public function it_validates_bot_message_filter_type(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';
        $invalidFilter = 'invalid_filter';

        // Assert
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage("Invalid botMessageFilterType 'invalid_filter'. Allowed values: include, exclude, only");

        // Act
        $this->client->download($startTime, $endTime, 'ja_JP', $invalidFilter);
    }

    /** @test */
    public function it_validates_roger_message_filter_type(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';
        $invalidFilter = 'invalid_filter';

        // Assert
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage("Invalid rogerMessageFilterType 'invalid_filter'. Allowed values: include, exclude, only");

        // Act
        $this->client->getDownloadUrlOnly($startTime, $endTime, 'ja_JP', null, null, $invalidFilter);
    }

    /** @test */
    public function it_throws_exception_when_location_header_missing(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';

        $redirectResponse = Mockery::mock(ResponseInterface::class);
        $redirectResponse->shouldReceive('getStatusCode')->andReturn(302);
        $redirectResponse->shouldReceive('getHeader')->with('Location')->andReturn([]);
        $redirectResponse->shouldReceive('getHeaders')->andReturn(['Content-Type' => ['application/json']]);

        $this->lineWorksClient->shouldReceive('get')
            ->andReturn($redirectResponse);

        // Assert
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Location header not found in 302 response');

        // Act
        $this->client->getDownloadUrlOnly($startTime, $endTime);
    }

    /** @test */
    public function it_throws_exception_when_not_302_response(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';

        $streamMock = Mockery::mock(StreamInterface::class);
        $streamMock->shouldReceive('__toString')->andReturn('{"error": "unexpected"}');

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('getBody')->andReturn($streamMock);
        $response->shouldReceive('getHeaders')->andReturn(['Content-Type' => ['application/json']]);

        $this->lineWorksClient->shouldReceive('get')
            ->andReturn($response);

        // Assert
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Expected 302 redirect with Location header, but got HTTP 200');

        // Act
        $this->client->getDownloadUrlOnly($startTime, $endTime);
    }

    /** @test */
    public function it_handles_download_with_all_optional_parameters(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';
        $language = 'en_US';
        $botFilter = 'only';
        $domainId = 12345;
        $rogerFilter = 'exclude';
        $downloadUrl = 'https://example.com/download/abc123';
        $csvContent = "datetime,sender,receiver,channel_id,message\n2025-05-30T18:24:53+09:00,[Bot]test,user@example.com,channel123,Hello World\n";

        $redirectResponse = Mockery::mock(ResponseInterface::class);
        $redirectResponse->shouldReceive('getStatusCode')->andReturn(302);
        $redirectResponse->shouldReceive('getHeader')->with('Location')->andReturn([$downloadUrl]);

        $downloadResponse = ['raw_response' => $csvContent];

        $this->lineWorksClient->shouldReceive('get')
            ->with('monitoring/message-contents/download', [
                'startTime' => $startTime,
                'endTime' => $endTime,
                'language' => $language,
                'botMessageFilterType' => $botFilter,
                'domainId' => $domainId,
                'rogerMessageFilterType' => $rogerFilter,
            ], [], ['allow_redirects' => false])
            ->andReturn($redirectResponse);

        $this->lineWorksClient->shouldReceive('getAccessTokenManager')
            ->andReturn($this->tokenManager);

        $this->tokenManager->shouldReceive('getToken')
            ->andReturn('test-access-token');

        $this->lineWorksClient->shouldReceive('requestExternal')
            ->andReturn($downloadResponse);

        // Act
        $response = $this->client->download($startTime, $endTime, $language, $botFilter, $domainId, $rogerFilter);

        // Assert
        $this->assertInstanceOf(MessageContentsResponse::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals($downloadUrl, $response->getDownloadUrl());
        
        $metadata = $response->getMetadata();
        $this->assertEquals($startTime, $metadata['startTime']);
        $this->assertEquals($endTime, $metadata['endTime']);
        $this->assertEquals($language, $metadata['language']);
        $this->assertEquals($botFilter, $metadata['botMessageFilterType']);
        $this->assertEquals($domainId, $metadata['domainId']);
        $this->assertEquals($rogerFilter, $metadata['rogerMessageFilterType']);
    }

    /** @test */
    public function it_handles_external_request_failure_in_download(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';
        $downloadUrl = 'https://example.com/download/abc123';

        $redirectResponse = Mockery::mock(ResponseInterface::class);
        $redirectResponse->shouldReceive('getStatusCode')->andReturn(302);
        $redirectResponse->shouldReceive('getHeader')->with('Location')->andReturn([$downloadUrl]);

        $this->lineWorksClient->shouldReceive('get')
            ->andReturn($redirectResponse);

        $this->lineWorksClient->shouldReceive('getAccessTokenManager')
            ->andReturn($this->tokenManager);

        $this->tokenManager->shouldReceive('getToken')
            ->andReturn('test-access-token');

        $originalException = new ApiException('External request failed', 0, null, 500);
        $this->lineWorksClient->shouldReceive('requestExternal')
            ->andThrow($originalException);

        // Assert
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to download message contents: External request failed');

        // Act
        $this->client->download($startTime, $endTime);
    }

    /** @test */
    public function it_correctly_url_encodes_time_parameters_in_get_download_url_only(): void
    {
        // Arrange
        $startTime = '2025-05-01T00:00:00+09:00';
        $endTime = '2025-05-31T23:59:59+09:00';
        $language = 'ja_JP';
        $downloadUrl = 'https://example.com/download/abc123';

        $redirectResponse = Mockery::mock(ResponseInterface::class);
        $redirectResponse->shouldReceive('getStatusCode')->andReturn(302);
        $redirectResponse->shouldReceive('getHeader')->with('Location')->andReturn([$downloadUrl]);

        $this->lineWorksClient->shouldReceive('get')
            ->with('monitoring/message-contents/download', [
                'startTime' => $startTime,
                'endTime' => $endTime,
                'language' => $language,
            ], [], ['allow_redirects' => false])
            ->andReturn($redirectResponse);

        // Act
        $result = $this->client->getDownloadUrlOnly($startTime, $endTime, $language);

        // Assert
        $this->assertEquals($downloadUrl, $result);
    }
} 