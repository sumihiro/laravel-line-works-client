<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\RichMenu;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

/**
 * リッチメニュー画像取得APIのレスポンスDTO
 * 
 * @see https://developers.worksmobile.com/jp/docs/bot-richmenu-image-get
 */
class GetImageResponse extends BaseDTO
{
    /**
     * 画像のバイナリデータを取得
     *
     * @return string|null
     */
    public function getImageData(): ?string
    {
        return $this->get('raw_response');
    }

    /**
     * 画像のContent-Typeを取得
     *
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->get('content_type');
    }

    /**
     * 画像データが存在するかどうかを判定
     *
     * @return bool
     */
    public function hasImage(): bool
    {
        return !empty($this->getImageData());
    }
} 