<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\Channel;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

class CreateChannelResponse extends BaseDTO
{
    /**
     * Get the channel ID.
     *
     * @return string|null
     */
    public function getChannelId(): ?string
    {
        return $this->get('channelId');
    }

    /**
     * Determine if the channel creation was successful.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->has('channelId');
    }
} 