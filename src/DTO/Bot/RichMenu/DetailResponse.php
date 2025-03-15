<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\RichMenu;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

/**
 * リッチメニュー詳細取得APIのレスポンスDTO
 * 
 * @see https://developers.worksmobile.com/jp/docs/bot-richmenu-get
 */
class DetailResponse extends BaseDTO
{
    /**
     * リッチメニューIDを取得
     *
     * @return string|null
     */
    public function getRichMenuId(): ?string
    {
        return $this->get('richmenuId');
    }

    /**
     * リッチメニュー名を取得
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->get('richmenuName');
    }

    /**
     * チャットバーのテキストを取得
     *
     * @return string|null
     */
    public function getChatBarText(): ?string
    {
        return $this->get('chatBarText');
    }

    /**
     * サイズを取得
     *
     * @return array<string, int>|null
     */
    public function getSize(): ?array
    {
        return $this->get('size');
    }

    /**
     * 幅を取得
     *
     * @return int|null
     */
    public function getWidth(): ?int
    {
        $size = $this->getSize();
        return $size['width'] ?? null;
    }

    /**
     * 高さを取得
     *
     * @return int|null
     */
    public function getHeight(): ?int
    {
        $size = $this->getSize();
        return $size['height'] ?? null;
    }

    /**
     * エリア情報を取得
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAreas(): array
    {
        return $this->get('areas', []);
    }

    /**
     * 選択状態を取得
     *
     * @return bool
     */
    public function isSelected(): bool
    {
        return (bool) $this->get('selected', false);
    }
} 