<?php

namespace Sumihiro\LineWorksClient\Exceptions;

class ConfigurationException extends LineWorksException
{
    /**
     * Create a new configuration exception instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @return void
     */
    public function __construct(
        string $message = 'LINE WORKS configuration error',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, null, null, $previous);
    }
} 