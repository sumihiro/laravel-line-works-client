<?php

namespace Sumihiro\LineWorksClient\DTO\Bot;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

class BotInfoResponse extends BaseDTO
{
    /**
     * Get the bot ID.
     *
     * @return string|null
     */
    public function getBotId(): ?string
    {
        return $this->data['botId'] ?? null;
    }

    /**
     * Get the bot name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    /**
     * Get the bot status.
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->data['status'] ?? null;
    }

    /**
     * Get the bot photo URL.
     *
     * @return string|null
     */
    public function getPhotoUrl(): ?string
    {
        return $this->data['photoUrl'] ?? null;
    }

    /**
     * Get the bot description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * Determine if the bot info operation was successful.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return isset($this->data['botId']);
    }
} 