<?php

namespace Sumihiro\LineWorksClient\Bot\RichMenu;

use Sumihiro\LineWorksClient\DTO\Bot\RichMenuResponse;
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\LineWorksClient;

class RichMenuClient
{
    /**
     * The LINE WORKS client instance.
     *
     * @var \Sumihiro\LineWorksClient\LineWorksClient
     */
    protected LineWorksClient $client;

    /**
     * Create a new rich menu client instance.
     *
     * @param \Sumihiro\LineWorksClient\LineWorksClient $client
     * @return void
     */
    public function __construct(LineWorksClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get the rich menu list.
     *
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function list(): RichMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu";

        $response = $this->client->get($endpoint);

        return new RichMenuResponse($response);
    }

    /**
     * Get a rich menu by ID.
     *
     * @param string $richMenuId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function get(string $richMenuId): RichMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu/{$richMenuId}";

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
    public function create(array $richMenu): RichMenuResponse
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
    public function delete(string $richMenuId): bool
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
    public function setForUser(string $accountId, string $richMenuId): bool
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
    public function getForUser(string $accountId): RichMenuResponse
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
    public function deleteForUser(string $accountId): bool
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
    public function uploadImage(string $richMenuId, string $imagePath): bool
    {
        if (!file_exists($imagePath)) {
            throw new ApiException("Image file not found: {$imagePath}");
        }

        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu/{$richMenuId}/content";

        // Get file mime type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($imagePath);

        // Prepare multipart form data
        $multipart = [
            [
                'name' => 'file',
                'contents' => fopen($imagePath, 'r'),
                'filename' => basename($imagePath),
                'headers' => [
                    'Content-Type' => $mimeType,
                ],
            ],
        ];

        $this->client->postMultipart($endpoint, $multipart);

        return true;
    }

    /**
     * Set the default rich menu.
     *
     * @param string $richMenuId
     * @return bool
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function setDefault(string $richMenuId): bool
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu/default";

        $this->client->post($endpoint, [
            'richMenuId' => $richMenuId,
        ]);

        return true;
    }

    /**
     * Get the default rich menu.
     *
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function getDefault(): RichMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu/default";

        $response = $this->client->get($endpoint);

        return new RichMenuResponse($response);
    }

    /**
     * Delete the default rich menu.
     *
     * @return bool
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function deleteDefault(): bool
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenu/default";

        $this->client->delete($endpoint);

        return true;
    }
} 