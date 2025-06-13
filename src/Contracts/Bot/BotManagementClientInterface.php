<?php

namespace Sumihiro\LineWorksClient\Contracts\Bot;

use Sumihiro\LineWorksClient\DTO\Bot\BotInfoResponse;

interface BotManagementClientInterface
{
    public function info(): BotInfoResponse;
    
    public function domainInfo(): array;
}