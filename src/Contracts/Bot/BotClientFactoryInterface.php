<?php

namespace Sumihiro\LineWorksClient\Contracts\Bot;

use Sumihiro\LineWorksClient\LineWorksClient;

interface BotClientFactoryInterface
{
    public function create(LineWorksClient $client): BotClientInterface;
}