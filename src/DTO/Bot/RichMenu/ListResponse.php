<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\RichMenu;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

/**
 * リッチメニュー一覧取得APIのレスポンスDTO
 * 
 * @see https://developers.worksmobile.com/jp/docs/bot-richmenu-list
 */
class ListResponse extends BaseDTO
{
    /**
     * リッチメニュー一覧を取得
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRichMenus(): array
    {
        return $this->get('richmenus', []);
    }

    /**
     * 次のカーソルを取得
     *
     * @return string|null
     */
    public function getNextCursor(): ?string
    {
        $metaData = $this->get('responseMetaData', []);
        return $metaData['nextCursor'] ?? null;
    }

    /**
     * リッチメニューの数を取得
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->getRichMenus());
    }

    /**
     * リッチメニューが存在するかどうかを判定
     *
     * @return bool
     */
    public function hasRichMenus(): bool
    {
        return $this->count() > 0;
    }
} 