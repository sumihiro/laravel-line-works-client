<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\Channel;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

class MembersResponse extends BaseDTO
{
    /**
     * Get the channel members.
     *
     * @return array<int, array<string, string>>|null
     */
    public function getMembers(): ?array
    {
        $members = $this->get('members');
        
        // メンバー情報がない場合はnullを返す
        if ($members === null) {
            return null;
        }
        
        // すでに配列の配列になっている場合はそのまま返す
        if (isset($members[0]) && is_array($members[0])) {
            return $members;
        }
        
        // 文字列の配列の場合は、各メンバーをオブジェクトに変換する
        return array_map(function ($memberId) {
            return ['accountId' => $memberId];
        }, $members);
    }

    /**
     * Get the channel member count.
     *
     * @return int|null
     */
    public function getMemberCount(): ?int
    {
        $members = $this->getMembers();
        return $members !== null ? count($members) : null;
    }

    /**
     * Get the next cursor for pagination.
     *
     * @return string|null
     */
    public function getNextCursor(): ?string
    {
        $metaData = $this->get('responseMetaData');
        return is_array($metaData) ? ($metaData['nextCursor'] ?? null) : null;
    }

    /**
     * Determine if the channel members operation was successful.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->has('members');
    }
} 