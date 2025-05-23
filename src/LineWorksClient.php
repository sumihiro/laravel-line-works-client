<?php

namespace Sumihiro\LineWorksClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Sumihiro\LineWorksClient\Auth\AccessTokenManager;
use Sumihiro\LineWorksClient\Auth\JwtTokenGenerator;
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\Exceptions\ConfigurationException;

class LineWorksClient
{
    /**
     * The HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected Client $client;

    /**
     * The access token manager instance.
     *
     * @var \Sumihiro\LineWorksClient\Auth\AccessTokenManager
     */
    protected AccessTokenManager $accessTokenManager;

    /**
     * The bot configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * The global configuration.
     *
     * @var array<string, mixed>
     */
    protected array $globalConfig;

    /**
     * The bot name.
     *
     * @var string
     */
    protected string $botName;

    /**
     * Custom logger callback.
     *
     * @var callable|null
     */
    protected $loggerCallback = null;

    /**
     * Create a new LINE WORKS client instance.
     *
     * @param string $botName
     * @param array<string, mixed> $config
     * @param array<string, mixed> $globalConfig
     * @return void
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    public function __construct(string $botName, array $config, array $globalConfig)
    {
        $this->botName = $botName;
        $this->config = $config;
        $this->globalConfig = $globalConfig;

        // Validate the configuration
        $this->validateConfig();

        // Create the HTTP client
        $this->client = new Client([
            'base_uri' => $globalConfig['api_base_url'],
            'timeout' => 30,
            'http_errors' => false,
        ]);

        // Create the JWT token generator
        $jwtTokenGenerator = new JwtTokenGenerator($config, $globalConfig);

        // Create the access token manager
        $this->accessTokenManager = new AccessTokenManager(
            $this->client,
            $jwtTokenGenerator,
            $config,
            $globalConfig
        );
    }

    /**
     * Set a custom logger callback.
     *
     * @param callable $callback
     * @return void
     */
    public function setLogger(callable $callback): void
    {
        $this->loggerCallback = $callback;
    }

    /**
     * Get the access token manager instance.
     *
     * @return \Sumihiro\LineWorksClient\Auth\AccessTokenManager
     */
    public function getAccessTokenManager(): AccessTokenManager
    {
        return $this->accessTokenManager;
    }

    /**
     * Get the bot name.
     *
     * @return string
     */
    public function getBotName(): string
    {
        return $this->botName;
    }

    /**
     * Get the bot configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get the bot ID.
     *
     * @return string
     */
    public function getBotId(): string
    {
        return $this->config['bot_id'];
    }

    /**
     * Get the domain ID.
     *
     * @return string
     */
    public function getDomainId(): string
    {
        return $this->config['domain_id'];
    }

    /**
     * Get an upload client instance.
     *
     * @return \Sumihiro\LineWorksClient\Upload\UploadClient
     */
    public function upload(): \Sumihiro\LineWorksClient\Upload\UploadClient
    {
        return new \Sumihiro\LineWorksClient\Upload\UploadClient($this);
    }

    /**
     * Get a bot client instance.
     *
     * @return \Sumihiro\LineWorksClient\Bot\BotClient
     */
    public function bot(): \Sumihiro\LineWorksClient\Bot\BotClient
    {
        return new \Sumihiro\LineWorksClient\Bot\BotClient($this);
    }

    /**
     * Send a GET request to the LINE WORKS API.
     *
     * @param string $endpoint
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function get(string $endpoint, array $query = [], array $headers = []): array
    {
        return $this->request('GET', $endpoint, [
            'query' => $query,
            'headers' => $headers,
        ]);
    }

    /**
     * Send a POST request to the LINE WORKS API.
     *
     * @param string $endpoint
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function post(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('POST', $endpoint, [
            'json' => $data,
            'headers' => $headers,
        ]);
    }

    /**
     * Send a PUT request to the LINE WORKS API.
     *
     * @param string $endpoint
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('PUT', $endpoint, [
            'json' => $data,
            'headers' => $headers,
        ]);
    }

    /**
     * Send a DELETE request to the LINE WORKS API.
     *
     * @param string $endpoint
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function delete(string $endpoint, array $query = [], array $headers = []): array
    {
        return $this->request('DELETE', $endpoint, [
            'query' => $query,
            'headers' => $headers,
        ]);
    }

    /**
     * Send a multipart POST request to the LINE WORKS API.
     *
     * @param string $endpoint
     * @param array<int, array<string, mixed>> $multipart
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function postMultipart(string $endpoint, array $multipart, array $headers = []): array
    {
        // Remove Content-Type header as it will be set automatically by Guzzle for multipart requests
        unset($headers['Content-Type']);

        return $this->request('POST', $endpoint, [
            'multipart' => $multipart,
            'headers' => $headers,
        ]);
    }

    /**
     * Send a request to the LINE WORKS API.
     *
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            // Get the access token
            $accessToken = $this->accessTokenManager->getToken();

            // Add the authorization header
            $options['headers'] = array_merge(
                $options['headers'] ?? [],
                [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ]
            );

            // Log the request if logging is enabled
            $this->logDebug('Sending request to LINE WORKS API', [
                'method' => $method,
                'endpoint' => $endpoint,
                'options' => $this->sanitizeOptions($options),
            ]);

            // Send the request
            $response = $this->client->request($method, $endpoint, $options);

            // Get the response body
            $body = (string) $response->getBody();
            $statusCode = $response->getStatusCode();

            // Try to parse the response body as JSON
            $responseData = [];
            if (!empty($body)) {
                $responseData = json_decode($body, true);
                // If JSON parsing failed, return the raw body
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $responseData = ['raw_response' => $body];
                }
            }

            // Log the response if logging is enabled
            $this->logDebug('Received response from LINE WORKS API', [
                'status_code' => $statusCode,
                'response' => $responseData,
            ]);

            // Check if the request was successful
            if ($statusCode < 200 || $statusCode >= 300) {
                $message = ($responseData['code'] ?? '') . ' ' . ($responseData['description'] ?? 'Unknown error');
                throw new ApiException(
                    'LINE WORKS API request failed: ' . $message,
                    0,
                    $responseData,
                    $statusCode
                );
            }

            return $responseData;
        } catch (GuzzleException $e) {
            // Handle Guzzle exceptions
            $statusCode = $e->getCode();
            $message = $e->getMessage();

            // Try to parse the response body if available
            $responseData = null;
            
            // GuzzleExceptionのサブクラスによってはgetResponseメソッドが存在しない場合があるため、
            // 例外処理を追加する
            try {
                // GuzzleExceptionのサブクラスを特定して処理する
                if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                    $response = $e->getResponse();
                    $responseBody = (string) $response->getBody();
                    $responseData = json_decode($responseBody, true);
                    $statusCode = $response->getStatusCode();
                }
            } catch (\Exception $innerException) {
                // 例外処理に失敗した場合は無視する
                $this->logError('Failed to process GuzzleException', [
                    'message' => $innerException->getMessage(),
                ]);
            }

            // Log the error if logging is enabled
            $this->logError('LINE WORKS API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'message' => $message,
                'status_code' => $statusCode,
                'response_data' => $responseData,
            ]);

            throw new ApiException(
                'LINE WORKS API request failed: ' . $message,
                0,
                $responseData,
                $statusCode,
                $e
            );
        }
    }

    /**
     * Sanitize the request options for logging.
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function sanitizeOptions(array $options): array
    {
        $sanitized = $options;

        // Remove sensitive information from headers
        if (isset($sanitized['headers']['Authorization'])) {
            $sanitized['headers']['Authorization'] = 'Bearer [REDACTED]';
        }

        return $sanitized;
    }

    /**
     * Validate the configuration.
     *
     * @return void
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    protected function validateConfig(): void
    {
        $requiredKeys = [
            'service_account',
            'private_key',
            'client_id',
            'client_secret',
            'bot_id',
            'domain_id',
        ];

        foreach ($requiredKeys as $key) {
            if (empty($this->config[$key])) {
                throw new ConfigurationException("The '{$key}' configuration is required for bot '{$this->botName}'");
            }
        }
    }

    /**
     * Log a debug message if logging is enabled.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    protected function logDebug(string $message, array $context = []): void
    {
        if ($this->isLoggingEnabled() && $this->getLogLevel() === 'debug') {
            $this->log('debug', $message, $context);
        }
    }

    /**
     * Log an error message if logging is enabled.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        if ($this->isLoggingEnabled()) {
            $this->log('error', $message, $context);
        }
    }

    /**
     * Log a message with the specified level.
     *
     * @param string $level
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        // カスタムロガーが設定されている場合はそれを使用
        if ($this->loggerCallback !== null) {
            call_user_func($this->loggerCallback, $level, '[LineWorksClient] [' . $this->botName . '] ' . $message, $context);
            return;
        }

        // Laravelのロギングが有効な場合はそれを使用
        if ($this->isLoggingEnabled()) {
            $channel = $this->globalConfig['logging']['channel'] ?? null;
            $logger = $channel ? Log::channel($channel) : Log::stack(['single']);
            
            $logger->{$level}('[LineWorksClient] [' . $this->botName . '] ' . $message, $context);
        }
    }

    /**
     * Determine if logging is enabled.
     *
     * @return bool
     */
    protected function isLoggingEnabled(): bool
    {
        return $this->globalConfig['logging']['enabled'] ?? false;
    }

    /**
     * Get the log level.
     *
     * @return string
     */
    protected function getLogLevel(): string
    {
        return $this->globalConfig['logging']['level'] ?? 'debug';
    }

    /**
     * Send a request to an external URL.
     *
     * @param string $method
     * @param string $url
     * @param array<string, mixed> $options
     * @return array<string, mixed>|string
     * @throws \Sumihiro\LineWorksClient\Exceptions\ApiException
     */
    public function requestExternal(string $method, string $url, array $options = []): array|string
    {
        try {
            // Get the access token
            $accessToken = $this->accessTokenManager->getToken();

            // Add the authorization header if not already set
            if (!isset($options['headers']['Authorization'])) {
                $options['headers'] = array_merge(
                    $options['headers'] ?? [],
                    [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ]
                );
            }

            // Log the request if logging is enabled
            $this->logDebug('Sending request to external URL', [
                'method' => $method,
                'url' => $url,
                'options' => $this->sanitizeOptions($options),
            ]);

            // Create a new Guzzle client for the external URL (without base_uri)
            $externalClient = new Client([
                'http_errors' => false,
            ]);

            // Send the request
            $response = $externalClient->request($method, $url, $options);

            // Get the response body
            $body = (string) $response->getBody();
            $statusCode = $response->getStatusCode();

            // Try to parse the response body as JSON
            $responseData = [];
            if (!empty($body)) {
                $responseData = json_decode($body, true);
                // If JSON parsing failed, return the raw body
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $responseData = ['raw_response' => $body];
                }
            }

            // Log the response if logging is enabled
            $this->logDebug('Received response from external URL', [
                'status_code' => $statusCode,
                'response' => $responseData,
            ]);

            // Check if the request was successful
            if ($statusCode < 200 || $statusCode >= 300) {
                throw new ApiException(
                    'External request failed: HTTP ' . $statusCode,
                    0,
                    $responseData,
                    $statusCode
                );
            }

            return $responseData ?: $body;
        } catch (GuzzleException $e) {
            // Handle Guzzle exceptions
            $statusCode = $e->getCode();
            $message = $e->getMessage();

            // Try to parse the response body if available
            $responseData = null;
            
            try {
                if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                    $response = $e->getResponse();
                    $responseBody = (string) $response->getBody();
                    $responseData = json_decode($responseBody, true);
                    $statusCode = $response->getStatusCode();
                }
            } catch (\Exception $innerException) {
                // Ignore inner exceptions
            }

            // Log the error if logging is enabled
            $this->logError('External request failed', [
                'url' => $url,
                'message' => $message,
                'status_code' => $statusCode,
                'response_data' => $responseData,
            ]);

            throw new ApiException(
                'External request failed: ' . $message,
                0,
                $responseData,
                $statusCode,
                $e
            );
        }
    }
} 