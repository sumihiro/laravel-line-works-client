<?php

namespace Sumihiro\LineWorksClient\DTO\Bot;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

class MessageResponse extends BaseDTO
{
    /**
     * Get the message ID.
     *
     * @return string|null
     */
    public function getMessageId(): ?string
    {
        return $this->get('messageId');
    }

    /**
     * Get the timestamp.
     *
     * @return int|null
     */
    public function getTimestamp(): ?int
    {
        return $this->get('timestamp');
    }

    /**
     * Determine if the message was sent successfully.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->has('messageId');
    }
} 