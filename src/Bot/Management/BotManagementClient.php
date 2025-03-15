<?php

namespace Sumihiro\LineWorksClient\Bot\Management;

use Sumihiro\LineWorksClient\DTO\Bot\BotInfoResponse;
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\LineWorksClient;

class BotManagementClient
{
    /**
     * The LINE WORKS client instance.
     *
     * @var \Sumihiro\LineWorksClient\LineWorksClient
     */
    protected LineWorksClient $client;

    /**
     * Create a new bot management client instance.
     *
     * @param \Sumihiro\LineWorksClient\LineWorksClient $client
     * @return void
     */
    public function __construct(LineWorksClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get the bot information.
     *
     * @return \Sumihiro\LineWorksClient\DTO\Bot\BotInfoResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function info(): BotInfoResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}";

        $response = $this->client->get($endpoint);

        return new BotInfoResponse($response);
    }

    /**
     * Get the domain information.
     *
     * @return array<string, mixed>
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function domainInfo(): array
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/domain";

        return $this->client->get($endpoint);
    }
} 