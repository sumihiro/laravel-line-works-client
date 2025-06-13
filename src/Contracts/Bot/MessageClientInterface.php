<?php

namespace Sumihiro\LineWorksClient\Contracts\Bot;

use Sumihiro\LineWorksClient\DTO\Bot\Message\MessageResponse;

interface MessageClientInterface
{
    public function sendText(string $accountId, string $content): MessageResponse;
    
    public function sendMessage(string $accountId, array $message): MessageResponse;
}