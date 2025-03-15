<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\RichMenu;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

/**
 * リッチメニュー画像設定APIのレスポンスDTO
 * 
 * @see https://developers.worksmobile.com/jp/docs/bot-richmenu-image-set
 */
class SetImageResponse extends BaseDTO
{
    /**
     * 画像設定が成功したかどうかを判定
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return true; // 画像設定APIは成功時に204を返し、例外が発生しなければ常に成功
    }
} 