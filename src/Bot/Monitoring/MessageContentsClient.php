<?php

namespace Sumihiro\LineWorksClient\Bot\Monitoring;

use Sumihiro\LineWorksClient\DTO\Bot\Monitoring\MessageContentsResponse;
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\LineWorksClient;

class MessageContentsClient
{
    /**
     * The LINE WORKS client instance.
     *
     * @var \Sumihiro\LineWorksClient\LineWorksClient
     */
    protected LineWorksClient $client;

    /**
     * Create a new message contents client instance.
     *
     * @param \Sumihiro\LineWorksClient\LineWorksClient $client
     * @return void
     */
    public function __construct(LineWorksClient $client)
    {
        $this->client = $client;
    }

    /**
     * Download message contents for monitoring.
     * This API follows a two-step process:
     * 1. GET request to the download endpoint returns 302 redirect
     * 2. Follow the redirect to download the actual content
     *
     * @param string $startTime Start time in YYYY-MM-DDThh:mm:ssTZD format (will be URL encoded)
     * @param string $endTime End time in YYYY-MM-DDThh:mm:ssTZD format (will be URL encoded)
     * @param string|null $language CSV file language (ja_JP, ko_KR, zh_CN, zh_TW, en_US)
     * @param string|null $botMessageFilterType Bot message filter type (include, exclude, only)
     * @param int|null $domainId Domain ID (for group companies to get logs from another domain)
     * @param string|null $rogerMessageFilterType Roger message filter type (include, exclude, only)
     * @return \Sumihiro\LineWorksClient\DTO\Bot\Monitoring\MessageContentsResponse
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function download(
        string $startTime,
        string $endTime,
        string $language = 'ja_JP',
        ?string $botMessageFilterType = null,
        ?int $domainId = null,
        ?string $rogerMessageFilterType = null
    ): MessageContentsResponse {
        $endpoint = "monitoring/message-contents/download";
        
        // Validate language parameter
        $this->validateLanguage($language);
        
        // Build query parameters
        $query = [
            'startTime' => $startTime,
            'endTime' => $endTime,
            'language' => $language,
        ];

        // Add optional parameters if provided
        if ($botMessageFilterType !== null) {
            $this->validateFilterType($botMessageFilterType, 'botMessageFilterType');
            $query['botMessageFilterType'] = $botMessageFilterType;
        }

        if ($domainId !== null) {
            $query['domainId'] = $domainId;
        }

        if ($rogerMessageFilterType !== null) {
            $this->validateFilterType($rogerMessageFilterType, 'rogerMessageFilterType');
            $query['rogerMessageFilterType'] = $rogerMessageFilterType;
        }

        // Step 1: Send initial GET request to get the redirect URL (302 response)
        // Note: LINE WORKS API returns 302 redirect, but we need to extract the Location header
        try {
            // We'll use a custom approach to handle the 302 redirect
            $downloadUrl = $this->getDownloadUrl($endpoint, $query);

            // Step 2: Follow the redirect to download the actual content
            $downloadResponse = $this->client->requestExternal('GET', $downloadUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->client->getAccessTokenManager()->getToken(),
                ],
            ]);
            
            return new MessageContentsResponse([
                'downloadUrl' => $downloadUrl,
                'content' => $downloadResponse,
                'metadata' => [
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'language' => $language,
                    'botMessageFilterType' => $botMessageFilterType,
                    'domainId' => $domainId,
                    'rogerMessageFilterType' => $rogerMessageFilterType,
                ],
            ]);
        } catch (ApiException $e) {
            // If the external request fails, wrap it with more context
            throw new ApiException(
                'Failed to download message contents: ' . $e->getMessage(),
                $e->getCode(),
                [
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'originalError' => $e->getResponseData(),
                ],
                $e->getStatusCode(),
                $e
            );
        }
    }

    /**
     * Get download URL for message contents.
     *
     * @param string $endpoint
     * @param array<string, mixed> $query
     * @return string The download URL
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    protected function getDownloadUrl(string $endpoint, array $query): string
    {
        // 302リダイレクトを無効にして生のレスポンスを取得
        $response = $this->client->get($endpoint, $query, [], ['allow_redirects' => false]);
        
        $statusCode = $response->getStatusCode();
        
        // Check if it's a 302 redirect
        if ($statusCode === 302) {
            $locationHeader = $response->getHeader('Location');
            if (!empty($locationHeader)) {
                return $locationHeader[0]; // Return the download URL
            }
            
            throw new ApiException(
                'Location header not found in 302 response',
                0,
                ['status_code' => $statusCode, 'headers' => $response->getHeaders()]
            );
        }

        // If we reach here, something unexpected happened
        throw new ApiException(
            'Expected 302 redirect with Location header, but got HTTP ' . $statusCode,
            0,
            [
                'status_code' => $statusCode,
                'response_body' => $response->getBody(),
                'headers' => $response->getHeaders()
            ]
        );
    }

    /**
     * Get message contents metadata without downloading the actual content.
     * This only performs the first step of the download process to get the download URL.
     *
     * @param string $startTime Start time in YYYY-MM-DDThh:mm:ssTZD format (will be URL encoded)
     * @param string $endTime End time in YYYY-MM-DDThh:mm:ssTZD format (will be URL encoded)
     * @param string|null $language CSV file language (ja_JP, ko_KR, zh_CN, zh_TW, en_US)
     * @param string|null $botMessageFilterType Bot message filter type (include, exclude, only)
     * @param int|null $domainId Domain ID (for group companies to get logs from another domain)
     * @param string|null $rogerMessageFilterType Roger message filter type (include, exclude, only)
     * @return string The download URL
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function getDownloadUrlOnly(
        string $startTime,
        string $endTime,
        string $language = 'ja_JP',
        ?string $botMessageFilterType = null,
        ?int $domainId = null,
        ?string $rogerMessageFilterType = null
    ): string {
        $endpoint = "monitoring/message-contents/download";
        
        // Validate language parameter
        $this->validateLanguage($language);
        
        // Build query parameters with URL encoding for time parameters
        $query = [
            'startTime' => $startTime,
            'endTime' => $endTime,
            'language' => $language,
        ];

        // Add optional parameters if provided
        if ($botMessageFilterType !== null) {
            $this->validateFilterType($botMessageFilterType, 'botMessageFilterType');
            $query['botMessageFilterType'] = $botMessageFilterType;
        }

        if ($domainId !== null) {
            $query['domainId'] = $domainId;
        }

        if ($rogerMessageFilterType !== null) {
            $this->validateFilterType($rogerMessageFilterType, 'rogerMessageFilterType');
            $query['rogerMessageFilterType'] = $rogerMessageFilterType;
        }

        return $this->getDownloadUrl($endpoint, $query);
    }

    /**
     * Validate language parameter.
     *
     * @param string $language
     * @return void
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    protected function validateLanguage(string $language): void
    {
        $allowedLanguages = ['ja_JP', 'ko_KR', 'zh_CN', 'zh_TW', 'en_US'];
        
        if (!in_array($language, $allowedLanguages)) {
            throw new ApiException(
                "Invalid language '{$language}'. Allowed values: " . implode(', ', $allowedLanguages),
                0,
                ['provided_language' => $language, 'allowed_languages' => $allowedLanguages]
            );
        }
    }

    /**
     * Validate filter type parameter.
     *
     * @param string $filterType
     * @param string $parameterName
     * @return void
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    protected function validateFilterType(string $filterType, string $parameterName): void
    {
        $allowedTypes = ['include', 'exclude', 'only'];
        
        if (!in_array($filterType, $allowedTypes)) {
            throw new ApiException(
                "Invalid {$parameterName} '{$filterType}'. Allowed values: " . implode(', ', $allowedTypes),
                0,
                ['provided_value' => $filterType, 'parameter_name' => $parameterName, 'allowed_values' => $allowedTypes]
            );
        }
    }
} 