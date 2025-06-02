<?php

namespace Sumihiro\LineWorksClient\Bot;

use Sumihiro\LineWorksClient\Bot\Attachment\AttachmentClient;
use Sumihiro\LineWorksClient\Bot\Channel\ChannelClient;
use Sumihiro\LineWorksClient\Bot\Management\BotManagementClient;
use Sumihiro\LineWorksClient\Bot\Message\MessageClient;
use Sumihiro\LineWorksClient\Bot\Monitoring\MessageContentsClient;
use Sumihiro\LineWorksClient\Bot\RichMenu\RichMenuClient;
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
     * The channel client instance.
     *
     * @var \Sumihiro\LineWorksClient\Bot\Channel\ChannelClient|null
     */
    protected ?ChannelClient $channelClient = null;

    /**
     * The message client instance.
     *
     * @var \Sumihiro\LineWorksClient\Bot\Message\MessageClient|null
     */
    protected ?MessageClient $messageClient = null;

    /**
     * The rich menu client instance.
     *
     * @var \Sumihiro\LineWorksClient\Bot\RichMenu\RichMenuClient|null
     */
    protected ?RichMenuClient $richMenuClient = null;

    /**
     * The bot management client instance.
     *
     * @var \Sumihiro\LineWorksClient\Bot\Management\BotManagementClient|null
     */
    protected ?BotManagementClient $botManagementClient = null;

    /**
     * The attachment client instance.
     *
     * @var \Sumihiro\LineWorksClient\Bot\Attachment\AttachmentClient|null
     */
    protected ?AttachmentClient $attachmentClient = null;

    /**
     * The monitoring client instance.
     *
     * @var \Sumihiro\LineWorksClient\Bot\Monitoring\MessageContentsClient|null
     */
    protected ?MessageContentsClient $monitoringClient = null;

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
     * Get the channel client instance.
     *
     * @return \Sumihiro\LineWorksClient\Bot\Channel\ChannelClient
     */
    public function channel(): ChannelClient
    {
        if ($this->channelClient === null) {
            $this->channelClient = new ChannelClient($this->client);
        }

        return $this->channelClient;
    }

    /**
     * Get the message client instance.
     *
     * @return \Sumihiro\LineWorksClient\Bot\Message\MessageClient
     */
    public function message(): MessageClient
    {
        if ($this->messageClient === null) {
            $this->messageClient = new MessageClient($this->client);
        }

        return $this->messageClient;
    }

    /**
     * Get the rich menu client instance.
     *
     * @return \Sumihiro\LineWorksClient\Bot\RichMenu\RichMenuClient
     */
    public function richMenu(): RichMenuClient
    {
        if ($this->richMenuClient === null) {
            $this->richMenuClient = new RichMenuClient($this->client);
        }

        return $this->richMenuClient;
    }

    /**
     * Get the attachment client instance.
     *
     * @return \Sumihiro\LineWorksClient\Bot\Attachment\AttachmentClient
     */
    public function attachment(): AttachmentClient
    {
        if ($this->attachmentClient === null) {
            $this->attachmentClient = new AttachmentClient($this->client);
        }

        return $this->attachmentClient;
    }

    /**
     * Get the bot management client instance.
     *
     * @return \Sumihiro\LineWorksClient\Bot\Management\BotManagementClient
     */
    public function management(): BotManagementClient
    {
        if ($this->botManagementClient === null) {
            $this->botManagementClient = new BotManagementClient($this->client);
        }

        return $this->botManagementClient;
    }

    /**
     * Get the monitoring client instance.
     *
     * @return \Sumihiro\LineWorksClient\Bot\Monitoring\MessageContentsClient
     */
    public function monitoring(): MessageContentsClient
    {
        if ($this->monitoringClient === null) {
            $this->monitoringClient = new MessageContentsClient($this->client);
        }

        return $this->monitoringClient;
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