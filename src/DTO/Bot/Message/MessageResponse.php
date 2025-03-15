<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\Message;

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
        // messageIdがある場合は成功
        if ($this->has('messageId')) {
            return true;
        }
        
        // レスポンスが空の場合も成功とみなす（LINE WORKS APIの仕様）
        if (empty($this->data)) {
            return true;
        }
        
        return false;
    }
} 