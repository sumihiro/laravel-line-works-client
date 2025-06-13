<?php

namespace Sumihiro\LineWorksClient\Bot\Message;

use Sumihiro\LineWorksClient\Contracts\Bot\MessageClientInterface;
use Sumihiro\LineWorksClient\DTO\Bot\Message\MessageResponse;
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\LineWorksClient;

class MessageClient implements MessageClientInterface
{
    /**
     * The LINE WORKS client instance.
     *
     * @var \Sumihiro\LineWorksClient\LineWorksClient
     */
    protected LineWorksClient $client;

    /**
     * Create a new message client instance.
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
     * @return \Sumihiro\LineWorksClient\DTO\Bot\Message\MessageResponse
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
     * @return \Sumihiro\LineWorksClient\DTO\Bot\Message\MessageResponse
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
} 