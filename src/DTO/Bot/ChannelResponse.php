<?php

namespace Sumihiro\LineWorksClient\DTO\Bot;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

class ChannelResponse extends BaseDTO
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
     * Get the channel name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->get('name');
    }

    /**
     * Get the channel type.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->get('type');
    }

    /**
     * Get the channel members.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function getMembers(): ?array
    {
        return $this->get('members');
    }

    /**
     * Get the channel member count.
     *
     * @return int|null
     */
    public function getMemberCount(): ?int
    {
        return $this->get('memberCount');
    }

    /**
     * Determine if the channel operation was successful.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->has('channelId');
    }
} 