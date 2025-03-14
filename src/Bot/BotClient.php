<?php

namespace Sumihiro\LineWorksClient\Bot;

use Sumihiro\LineWorksClient\DTO\Bot\MessageResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenuResponse;
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\LineWorksClient;

class BotClient
{
    /**
     * The LINE WORKS client instance.
     *
     * @var \Sumihiro\LineWorksClient\LineWorksClient
     */
    protected LineWorksClient $client;

    /**
     * Create a new bot client instance.
     *
     * @param \Sumihiro\LineWorksClient\LineWorksClient $client
     * @return void
     */
    public function __construct(LineWorksClient $client)
    {
        $this->client = $client;
    }

    /**
     * Send a text message.
     *
     * @param string $accountId
     * @param string $content
     * @return \Sumihiro\LineWorksClient\DTO\Bot\MessageResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function sendText(string $accountId, string $content): MessageResponse
    {
        return $this->sendMessage($accountId, [
            'content' => [
                'type' => 'text',
                'text' => $content,
            ],
        ]);
    }

    /**
     * Send a message.
     *
     * @param string $accountId
     * @param array<string, mixed> $message
     * @return \Sumihiro\LineWorksClient\DTO\Bot\MessageResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function sendMessage(string $accountId, array $message): MessageResponse
    {
        $botId = $this->client->getBotId();
        $domainId = $this->client->getDomainId();

        $endpoint = "bots/{$botId}/users/{$accountId}/messages";
        $data = array_merge($message, [
            'botId' => $botId,
            'accountId' => $accountId,
            'domainId' => $domainId,
        ]);

        $response = $this->client->post($endpoint, $data);

        return new MessageResponse($response);
    }

    /**
     * Send a message to a channel.
     *
     * @param string $channelId
     * @param array<string, mixed> $message
     * @return \Sumihiro\LineWorksClient\DTO\Bot\MessageResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function sendMessageToChannel(string $channelId, array $message): MessageResponse
    {
        $botId = $this->client->getBotId();
        $domainId = $this->client->getDomainId();

        $endpoint = "bots/{$botId}/channels/{$channelId}/messages";
        $data = array_merge($message, [
            'botId' => $botId,
            'channelId' => $channelId,
            'domainId' => $domainId,
        ]);

        $response = $this->client->post($endpoint, $data);

        return new MessageResponse($response);
    }

    /**
     * Get the rich menu list.
     *
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function getRichMenuList(): RichMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu";

        $response = $this->client->get($endpoint);

        return new RichMenuResponse($response);
    }

    /**
     * Create a rich menu.
     *
     * @param array<string, mixed> $richMenu
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function createRichMenu(array $richMenu): RichMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu";

        $response = $this->client->post($endpoint, $richMenu);

        return new RichMenuResponse($response);
    }

    /**
     * Delete a rich menu.
     *
     * @param string $richMenuId
     * @return bool
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function deleteRichMenu(string $richMenuId): bool
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu/{$richMenuId}";

        $this->client->delete($endpoint);

        return true;
    }

    /**
     * Set a rich menu for a user.
     *
     * @param string $accountId
     * @param string $richMenuId
     * @return bool
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function setRichMenuForUser(string $accountId, string $richMenuId): bool
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/users/{$accountId}/richmenu";

        $this->client->post($endpoint, [
            'richMenuId' => $richMenuId,
        ]);

        return true;
    }

    /**
     * Get the rich menu for a user.
     *
     * @param string $accountId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function getRichMenuForUser(string $accountId): RichMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/users/{$accountId}/richmenu";

        $response = $this->client->get($endpoint);

        return new RichMenuResponse($response);
    }

    /**
     * Delete the rich menu for a user.
     *
     * @param string $accountId
     * @return bool
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function deleteRichMenuForUser(string $accountId): bool
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/users/{$accountId}/richmenu";

        $this->client->delete($endpoint);

        return true;
    }

    /**
     * Upload a rich menu image.
     *
     * @param string $richMenuId
     * @param string $imagePath
     * @return bool
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function uploadRichMenuImage(string $richMenuId, string $imagePath): bool
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu/{$richMenuId}/content";

        // TODO: Implement file upload

        return true;
    }

    /**
     * Get the bot information.
     *
     * @return array<string, mixed>
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function getBotInfo(): array
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}";

        return $this->client->get($endpoint);
    }

    /**
     * Get the LINE WORKS client instance.
     *
     * @return \Sumihiro\LineWorksClient\LineWorksClient
     */
    public function getClient(): LineWorksClient
    {
        return $this->client;
    }
} 