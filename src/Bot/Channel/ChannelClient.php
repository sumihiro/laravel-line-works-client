<?php

namespace Sumihiro\LineWorksClient\Bot\Channel;

use Sumihiro\LineWorksClient\Contracts\Bot\ChannelClientInterface;
use Sumihiro\LineWorksClient\DTO\Bot\Channel\CreateChannelResponse;
use Sumihiro\LineWorksClient\DTO\Bot\Channel\InfoResponse;
use Sumihiro\LineWorksClient\DTO\Bot\Channel\MembersResponse;
use Sumihiro\LineWorksClient\DTO\Bot\Message\MessageResponse;
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\LineWorksClient;

class ChannelClient implements ChannelClientInterface
{
    /**
     * The LINE WORKS client instance.
     *
     * @var \Sumihiro\LineWorksClient\LineWorksClient
     */
    protected LineWorksClient $client;

    /**
     * Create a new channel client instance.
     *
     * @param \Sumihiro\LineWorksClient\LineWorksClient $client
     * @return void
     */
    public function __construct(LineWorksClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a channel with the bot.
     *
     * @param array<string, mixed> $accountIds
     * @param string|null $title
     * @return \Sumihiro\LineWorksClient\DTO\Bot\Channel\CreateChannelResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function create(array $accountIds, ?string $title = null): CreateChannelResponse
    {
        $botId = $this->client->getBotId();

        $endpoint = "bots/{$botId}/channels";
        $data = [
            'members' => $accountIds,
        ];

        if ($title !== null) {
            $data['title'] = $title;
        }

        $response = $this->client->post($endpoint, $data);

        return new CreateChannelResponse($response);
    }

    /**
     * Get channel information.
     *
     * @param string $channelId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\Channel\InfoResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function info(string $channelId): InfoResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/channels/{$channelId}";

        $response = $this->client->get($endpoint);

        return new InfoResponse($response);
    }

    /**
     * Leave a channel.
     *
     * @param string $channelId
     * @return bool
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function leave(string $channelId): bool
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/channels/{$channelId}/leave";

        $this->client->post($endpoint);

        return true;
    }

    /**
     * Get channel member list.
     *
     * @param string $channelId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\Channel\MembersResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function members(string $channelId): MembersResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/channels/{$channelId}/members";

        $response = $this->client->get($endpoint);

        return new MembersResponse($response);
    }

    /**
     * Send a message to a channel.
     *
     * @param string $channelId
     * @param array<string, mixed> $message
     * @return \Sumihiro\LineWorksClient\DTO\Bot\Message\MessageResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function sendMessage(string $channelId, array $message): MessageResponse
    {
        $botId = $this->client->getBotId();

        $endpoint = "bots/{$botId}/channels/{$channelId}/messages";

        $response = $this->client->post($endpoint, $message);

        return new MessageResponse($response);
    }
} 