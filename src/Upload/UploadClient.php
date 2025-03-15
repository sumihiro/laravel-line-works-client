<?php

namespace Sumihiro\LineWorksClient\Upload;

use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\LineWorksClient;

class UploadClient
{
    /**
     * The LINE WORKS client instance.
     *
     * @var \Sumihiro\LineWorksClient\LineWorksClient
     */
    protected LineWorksClient $client;

    /**
     * Create a new upload client instance.
     *
     * @param \Sumihiro\LineWorksClient\LineWorksClient $client
     * @return void
     */
    public function __construct(LineWorksClient $client)
    {
        $this->client = $client;
    }

    /**
     * Upload a file to the provided upload URL.
     *
     * @param string $uploadUrl
     * @param string $filePath
     * @param string|null $contentType
     * @return bool
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function upload(string $uploadUrl, string $filePath, ?string $contentType = null): bool
    {
        if (!file_exists($filePath)) {
            throw new ApiException("File not found: {$filePath}");
        }
        
        // Get file mime type if not provided
        if ($contentType === null) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $contentType = $finfo->file($filePath);
        }
        
        // サンプルリクエストに合わせてPOSTメソッドを使用し、multipart/form-dataでファイルを送信
        $options = [
            'multipart' => [
                [
                    'name' => 'Filedata',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath),
                    'headers' => [
                        'Content-Type' => $contentType,
                    ]
                ],
                [
                    'name' => 'resourceName',
                    'contents' => basename($filePath)
                ]
            ]
        ];
        
        // Use the LineWorksClient to send the request to the external URL
        try {
            // アクセストークンを取得して認証ヘッダーを追加
            $accessToken = $this->client->getAccessTokenManager()->getToken();
            $options['headers'] = [
                'Authorization' => 'Bearer ' . $accessToken,
            ];
            
            $this->client->requestExternal('POST', $uploadUrl, $options);
            return true;
        } catch (\Exception $e) {
            if ($e instanceof ApiException) {
                throw $e;
            }
            throw new ApiException("Failed to upload file: " . $e->getMessage(), 0, null, 0, $e);
        }
    }
} 