<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\RichMenu;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

/**
 * デフォルトリッチメニュー設定・取得・削除に関するレスポンスDTO
 */
class DefaultMenuResponse extends BaseDTO
{
    /**
     * リッチメニューIDを取得
     *
     * @return string|null
     */
    public function getRichMenuId(): ?string
    {
        return $this->get('richMenuId');
    }

    /**
     * 操作が成功したかどうかを判定
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        // 設定・削除APIは成功時に204を返し、例外が発生しなければ常に成功
        // 取得APIは成功時にrichMenuIdを含むJSONを返す
        return $this->has('richMenuId') || $this->get('success', true);
    }
} 