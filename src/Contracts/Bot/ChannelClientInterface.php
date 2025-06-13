<?php

namespace Sumihiro\LineWorksClient\Contracts\Bot;

use Sumihiro\LineWorksClient\DTO\Bot\Channel\CreateChannelResponse;
use Sumihiro\LineWorksClient\DTO\Bot\Channel\InfoResponse;
use Sumihiro\LineWorksClient\DTO\Bot\Channel\MembersResponse;
use Sumihiro\LineWorksClient\DTO\Bot\Message\MessageResponse;

interface ChannelClientInterface
{
    public function create(array $accountIds, ?string $title = null): CreateChannelResponse;
    
    public function info(string $channelId): InfoResponse;
    
    public function leave(string $channelId): bool;
    
    public function members(string $channelId): MembersResponse;
    
    public function sendMessage(string $channelId, array $message): MessageResponse;
}