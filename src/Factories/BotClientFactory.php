<?php

namespace Sumihiro\LineWorksClient\Factories;

use Sumihiro\LineWorksClient\Bot\BotClient;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientFactoryInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientInterface;
use Sumihiro\LineWorksClient\LineWorksClient;

class BotClientFactory implements BotClientFactoryInterface
{
    public function create(LineWorksClient $client): BotClientInterface
    {
        return new BotClient($client);
    }
}