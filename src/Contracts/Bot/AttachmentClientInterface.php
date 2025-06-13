<?php

namespace Sumihiro\LineWorksClient\Contracts\Bot;

use Sumihiro\LineWorksClient\DTO\Bot\Attachment\CreateResponse;

interface AttachmentClientInterface
{
    public function create(string $filePath, ?string $contentType = null): CreateResponse;
}