<?php

namespace Sumihiro\LineWorksClient\Bot\Attachment;

use Sumihiro\LineWorksClient\Contracts\Bot\AttachmentClientInterface;
use Sumihiro\LineWorksClient\DTO\Bot\Attachment\CreateResponse;
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\LineWorksClient;

class AttachmentClient implements AttachmentClientInterface
{
    /**
     * The LINE WORKS client instance.
     *
     * @var \Sumihiro\LineWorksClient\LineWorksClient
     */
    protected LineWorksClient $client;

    /**
     * Create a new attachment client instance.
     *
     * @param \Sumihiro\LineWorksClient\LineWorksClient $client
     * @return void
     */
    public function __construct(LineWorksClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create an attachment and get upload URL.
     *
     * @param string $filePath
     * @param string|null $contentType
     * @return \Sumihiro\LineWorksClient\DTO\Bot\Attachment\CreateResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function create(string $filePath, ?string $contentType = null): CreateResponse
    {
        if (!file_exists($filePath)) {
            throw new ApiException("File not found: {$filePath}");
        }

        $botId = $this->client->getBotId();
        
        // Get file mime type if not provided
        if ($contentType === null) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $contentType = $finfo->file($filePath);
        }
        
        // Create attachment and get upload URL
        $attachmentEndpoint = "bots/{$botId}/attachments";
        
        $attachmentResponse = $this->client->post($attachmentEndpoint, [
            'fileName' => basename($filePath),
            'size' => filesize($filePath),
            'contentType' => $contentType
        ]);
        
        return new CreateResponse($attachmentResponse);
    }
} 