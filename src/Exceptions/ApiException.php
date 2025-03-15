<?php

namespace Sumihiro\LineWorksClient\Exceptions;

class ApiException extends LineWorksException
{
    /**
     * @var string|null
     */
    protected ?string $requestUrl = null;

    /**
     * Create a new API exception instance.
     *
     * @param string $message
     * @param int $code
     * @param array<string, mixed>|null $responseData
     * @param int|null $statusCode
     * @param \Throwable|null $previous
     * @param string|null $requestUrl
     * @return void
     */
    public function __construct(
        string $message = 'LINE WORKS API request failed',
        int $code = 0,
        ?array $responseData = null,
        ?int $statusCode = null,
        ?\Throwable $previous = null,
        ?string $requestUrl = null
    ) {
        parent::__construct($message, $code, $responseData, $statusCode, $previous);
        $this->requestUrl = $requestUrl;
    }

    /**
     * Get the request URL.
     *
     * @return string|null
     */
    public function getRequestUrl(): ?string
    {
        return $this->requestUrl;
    }
} 