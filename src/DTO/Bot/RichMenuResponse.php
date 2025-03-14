<?php

namespace Sumihiro\LineWorksClient\DTO\Bot;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

class RichMenuResponse extends BaseDTO
{
    /**
     * Get the rich menu ID.
     *
     * @return string|null
     */
    public function getRichMenuId(): ?string
    {
        return $this->get('richMenuId');
    }

    /**
     * Get the rich menu name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->get('name');
    }

    /**
     * Get the rich menu size.
     *
     * @return array<string, int>|null
     */
    public function getSize(): ?array
    {
        return $this->get('size');
    }

    /**
     * Get the rich menu areas.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function getAreas(): ?array
    {
        return $this->get('areas');
    }

    /**
     * Get the rich menu list.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRichMenuList(): array
    {
        return $this->get('richMenus', []);
    }

    /**
     * Determine if the rich menu was created successfully.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->has('richMenuId');
    }
} 