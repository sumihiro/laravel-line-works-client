<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\RichMenu;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

/**
 * リッチメニュー作成APIのレスポンスDTO
 * 
 * @see https://developers.worksmobile.com/jp/docs/bot-richmenu-create
 */
class CreateResponse extends BaseDTO
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
     * 作成が成功したかどうかを判定
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->has('richmenuId') && !empty($this->getRichMenuId());
    }
} 