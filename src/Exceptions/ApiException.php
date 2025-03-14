<?php

namespace Sumihiro\LineWorksClient\Exceptions;

class ApiException extends LineWorksException
{
    /**
     * Create a new API exception instance.
     *
     * @param string $message
     * @param int $code
     * @param array<string, mixed>|null $responseData
     * @param int|null $statusCode
     * @param \Throwable|null $previous
     * @return void
     */
    public function __construct(
        string $message = 'LINE WORKS API request failed',
        int $code = 0,
        ?array $responseData = null,
        ?int $statusCode = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $responseData, $statusCode, $previous);
    }
} 