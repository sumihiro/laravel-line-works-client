<?php

namespace Sumihiro\LineWorksClient\Exceptions;

use Exception;

class LineWorksException extends Exception
{
    /**
     * @var array<string, mixed>|null
     */
    protected ?array $responseData = null;

    /**
     * @var int|null
     */
    protected ?int $statusCode = null;

    /**
     * Create a new LINE WORKS exception instance.
     *
     * @param string $message
     * @param int $code
     * @param array<string, mixed>|null $responseData
     * @param int|null $statusCode
     * @param \Throwable|null $previous
     * @return void
     */
    public function __construct(
        string $message = 'LINE WORKS API error occurred',
        int $code = 0,
        ?array $responseData = null,
        ?int $statusCode = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->responseData = $responseData;
        $this->statusCode = $statusCode;
    }

    /**
     * Get the response data from the API.
     *
     * @return array<string, mixed>|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
} 