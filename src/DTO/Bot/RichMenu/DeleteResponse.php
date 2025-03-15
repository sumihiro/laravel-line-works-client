<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\RichMenu;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

/**
 * リッチメニュー削除APIのレスポンスDTO
 * 
 * @see https://developers.worksmobile.com/jp/docs/bot-richmenu-delete
 */
class DeleteResponse extends BaseDTO
{
    /**
     * 削除が成功したかどうかを判定
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return true; // 削除APIは成功時に204を返し、例外が発生しなければ常に成功
    }
} 