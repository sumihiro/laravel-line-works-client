<?php

namespace Sumihiro\LineWorksClient\DTO\Bot\Attachment;

use Sumihiro\LineWorksClient\DTO\BaseDTO;

/**
 * アタッチメント作成APIのレスポンスDTO
 * 
 * @see https://developers.worksmobile.com/jp/docs/bot-attachment-create
 */
class CreateResponse extends BaseDTO
{
    /**
     * アップロードURLを取得
     *
     * @return string|null
     */
    public function getUploadUrl(): ?string
    {
        return $this->get('uploadUrl');
    }

    /**
     * ファイルIDを取得
     *
     * @return string|null
     */
    public function getFileId(): ?string
    {
        return $this->get('fileId');
    }

    /**
     * 作成が成功したかどうかを判定
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->has('uploadUrl') && $this->has('fileId') && 
               !empty($this->getUploadUrl()) && !empty($this->getFileId());
    }
} 