<?php

namespace Tests\Unit\Bot\Attachment;

use Mockery;
use PHPUnit\Framework\TestCase;
use Sumihiro\LineWorksClient\Bot\Attachment\AttachmentClient;
use Sumihiro\LineWorksClient\DTO\Bot\Attachment\CreateResponse;
use Sumihiro\LineWorksClient\LineWorksClient;

class AttachmentClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_create_attachment()
    {
        $mockClient = Mockery::mock(LineWorksClient::class);
        $mockClient->shouldReceive('getBotId')->andReturn('test-bot-id');
        
        // Mock creating attachment
        $mockClient->shouldReceive('post')->once()->with(
            'bots/test-bot-id/attachments',
            Mockery::on(function ($data) {
                return isset($data['fileName']) && isset($data['size']) && isset($data['contentType']);
            })
        )->andReturn([
            'uploadUrl' => 'https://example.com/upload',
            'fileId' => 'test-file-id'
        ]);

        // Create a temporary test file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_file_');
        file_put_contents($tempFile, 'test file content');

        $attachmentClient = new AttachmentClient($mockClient);
        $result = $attachmentClient->create($tempFile, 'text/plain');

        $this->assertInstanceOf(CreateResponse::class, $result);
        $this->assertEquals('https://example.com/upload', $result->getUploadUrl());
        $this->assertEquals('test-file-id', $result->getFileId());
        $this->assertTrue($result->isSuccess());

        // Clean up the temporary file
        unlink($tempFile);
    }
} 