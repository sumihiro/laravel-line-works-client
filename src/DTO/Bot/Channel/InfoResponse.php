<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\Channel;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

class InfoResponse extends BaseDTO
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
     * Get the channel title.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->get('title');
    }

    /**
     * Get the channel type.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        $channelType = $this->get('channelType');
        return is_array($channelType) ? ($channelType['type'] ?? null) : null;
    }

    /**
     * Get the domain ID.
     *
     * @return int|null
     */
    public function getDomainId(): ?int
    {
        return $this->get('domainId');
    }

    /**
     * Get the channel creation time.
     *
     * @return int|null
     */
    public function getCreatedTime(): ?int
    {
        return $this->get('createdTime');
    }

    /**
     * Get the channel status.
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->get('status');
    }

    /**
     * Determine if the channel info operation was successful.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->has('channelId');
    }
} 