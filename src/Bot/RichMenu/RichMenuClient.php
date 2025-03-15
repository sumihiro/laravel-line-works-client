<?php

namespace Sumihiro\LineWorksClient\Bot\RichMenu;

use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\CreateResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DefaultMenuResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DeleteResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DetailResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\GetImageResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\ListResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\SetImageResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\UserMenuResponse;
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
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\ListResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function list(): ListResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenus";

        $response = $this->client->get($endpoint);

        return new ListResponse($response);
    }

    /**
     * Get a rich menu by ID.
     *
     * @param string $richMenuId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DetailResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function get(string $richMenuId): DetailResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenus/{$richMenuId}";

        $response = $this->client->get($endpoint);

        return new DetailResponse($response);
    }

    /**
     * Create a rich menu.
     *
     * @param array<string, mixed> $richMenu
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\CreateResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function create(array $richMenu): CreateResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenus";

        $response = $this->client->post($endpoint, $richMenu);

        return new CreateResponse($response);
    }

    /**
     * Delete a rich menu.
     *
     * @param string $richMenuId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DeleteResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function delete(string $richMenuId): DeleteResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenus/{$richMenuId}";

        $this->client->delete($endpoint);

        return new DeleteResponse(['success' => true]);
    }

    /**
     * Set a rich menu for a user.
     *
     * @param string $accountId
     * @param string $richMenuId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\UserMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function setForUser(string $accountId, string $richMenuId): UserMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/users/{$accountId}/richmenus";

        $this->client->post($endpoint, [
            'richMenuId' => $richMenuId,
        ]);

        return new UserMenuResponse(['success' => true]);
    }

    /**
     * Get the rich menu for a user.
     *
     * @param string $accountId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\UserMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function getForUser(string $accountId): UserMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/users/{$accountId}/richmenus";

        $response = $this->client->get($endpoint);

        return new UserMenuResponse($response);
    }

    /**
     * Delete the rich menu for a user.
     *
     * @param string $accountId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\UserMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function deleteForUser(string $accountId): UserMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/users/{$accountId}/richmenus";

        $this->client->delete($endpoint);

        return new UserMenuResponse(['success' => true]);
    }

    /**
     * Set rich menu image using file ID.
     *
     * @param string $richMenuId
     * @param string $fileId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\SetImageResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function setRichMenuImage(string $richMenuId, string $fileId): SetImageResponse
    {
        $botId = $this->client->getBotId();
        $richMenuImageEndpoint = "bots/{$botId}/richmenus/{$richMenuId}/content";
        
        $this->client->post($richMenuImageEndpoint, [
            'fileId' => $fileId
        ]);
        
        return new SetImageResponse(['success' => true]);
    }

    /**
     * Get rich menu image.
     *
     * @param string $richMenuId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\GetImageResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function getImage(string $richMenuId): GetImageResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenus/{$richMenuId}/content";

        // 画像取得はバイナリデータを返すため、特別な処理が必要
        $response = $this->client->get($endpoint, [], ['Accept' => 'image/*']);
        
        // レスポンスヘッダーからContent-Typeを取得
        $contentType = $response['content_type'] ?? 'application/octet-stream';
        
        return new GetImageResponse([
            'raw_response' => $response['raw_response'] ?? null,
            'content_type' => $contentType,
        ]);
    }

    /**
     * Upload a rich menu image.
     *
     * @param string $richMenuId
     * @param string $imagePath
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\SetImageResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function uploadImage(string $richMenuId, string $imagePath): SetImageResponse
    {
        if (!file_exists($imagePath)) {
            throw new ApiException("Image file not found: {$imagePath}");
        }

        // Get file mime type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($imagePath);
        
        // Get attachment client from BotClient
        $attachmentClient = $this->client->bot()->attachment();
        
        // Step 1: Create attachment and get upload URL
        $attachmentResponse = $attachmentClient->create($imagePath, $mimeType);
        $uploadUrl = $attachmentResponse->getUploadUrl();
        $fileId = $attachmentResponse->getFileId();
        
        // Step 2: Upload file to the provided URL using UploadClient
        $this->client->upload()->upload($uploadUrl, $imagePath, $mimeType);
        
        // Step 3: Set the rich menu image using the file ID
        $this->setRichMenuImage($richMenuId, $fileId);
        
        return new SetImageResponse(['success' => true]);
    }

    /**
     * Set the default rich menu.
     *
     * @param string $richMenuId
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DefaultMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function setDefault(string $richMenuId): DefaultMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenus/default";

        $this->client->post($endpoint, [
            'richMenuId' => $richMenuId,
        ]);

        return new DefaultMenuResponse(['success' => true]);
    }

    /**
     * Get the default rich menu.
     *
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DefaultMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function getDefault(): DefaultMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenus/default";

        $response = $this->client->get($endpoint);

        return new DefaultMenuResponse($response);
    }

    /**
     * Delete the default rich menu.
     *
     * @return \Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DefaultMenuResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function deleteDefault(): DefaultMenuResponse
    {
        $botId = $this->client->getBotId();
        $endpoint = "bots/{$botId}/richmenus/default";

        $this->client->delete($endpoint);

        return new DefaultMenuResponse(['success' => true]);
    }
} 