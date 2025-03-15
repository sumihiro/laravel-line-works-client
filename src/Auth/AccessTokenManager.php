<?php

namespace Sumihiro\LineWorksClient\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Sumihiro\LineWorksClient\Exceptions\AuthenticationException;

class AccessTokenManager
{
    /**
     * The HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected Client $client;

    /**
     * The JWT token generator instance.
     *
     * @var \Sumihiro\LineWorksClient\Auth\JwtTokenGenerator
     */
    protected JwtTokenGenerator $jwtTokenGenerator;

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
     * Custom logger callback.
     *
     * @var callable|null
     */
    protected $loggerCallback = null;

    /**
     * Create a new access token manager instance.
     *
     * @param \GuzzleHttp\Client $client
     * @param \Sumihiro\LineWorksClient\Auth\JwtTokenGenerator $jwtTokenGenerator
     * @param array<string, mixed> $config
     * @param array<string, mixed> $globalConfig
     * @return void
     */
    public function __construct(
        Client $client,
        JwtTokenGenerator $jwtTokenGenerator,
        array $config,
        array $globalConfig
    ) {
        $this->client = $client;
        $this->jwtTokenGenerator = $jwtTokenGenerator;
        $this->config = $config;
        $this->globalConfig = $globalConfig;
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
     * Get an access token.
     *
     * @return string
     * @throws \Sumihiro\LineWorksClient\Exceptions\AuthenticationException
     */
    public function getToken(): string
    {
        // Check if caching is enabled and token exists in cache
        if ($this->shouldUseCache()) {
            $cacheKey = $this->getCacheKey();
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
        }

        // Generate a new token
        $token = $this->requestAccessToken();

        // Cache the token if caching is enabled
        if ($this->shouldUseCache()) {
            $this->cacheToken($token);
        }

        return $token;
    }

    /**
     * Request an access token from the LINE WORKS API.
     *
     * @return string
     * @throws \Sumihiro\LineWorksClient\Exceptions\AuthenticationException
     */
    protected function requestAccessToken(): string
    {
        try {
            // Generate JWT token
            $jwtToken = $this->jwtTokenGenerator->generate();

            // Get the scope from the configuration
            $scope = $this->config['scope'] ?? 'bot';

            // Log the request if logging is enabled
            $this->logDebug('Requesting access token from LINE WORKS API', [
                'client_id' => $this->config['client_id'],
                'scope' => $scope,
            ]);

            // Make the request to the LINE WORKS API
            $response = $this->client->post('https://auth.worksmobile.com/oauth2/v2.0/token', [
                'form_params' => [
                    'assertion' => $jwtToken,
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                    'scope' => $scope,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            // Parse the response
            $responseData = json_decode((string) $response->getBody(), true);

            // Log the response if logging is enabled
            $this->logDebug('Received access token response', ['response' => $responseData]);

            // Check if the response contains an access token
            if (!isset($responseData['access_token'])) {
                throw new AuthenticationException(
                    'Access token not found in response',
                    0,
                    $responseData,
                    $response->getStatusCode()
                );
            }

            return $responseData['access_token'];
        } catch (GuzzleException $e) {
            // Handle Guzzle exceptions
            $statusCode = $e->getCode();
            $message = $e->getMessage();

            // Try to parse the response body if available
            $responseData = null;
            
            // GuzzleExceptionのサブクラスを特定して処理する
            try {
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
            $this->logError('Failed to request access token', [
                'message' => $message,
                'status_code' => $statusCode,
                'response_data' => $responseData,
            ]);

            throw new AuthenticationException(
                'Failed to request access token: ' . $message,
                0,
                $responseData,
                $statusCode,
                $e
            );
        } catch (\Exception $e) {
            // Handle other exceptions
            $this->logError('Failed to request access token', [
                'message' => $e->getMessage(),
            ]);

            throw new AuthenticationException(
                'Failed to request access token: ' . $e->getMessage(),
                0,
                null,
                null,
                $e
            );
        }
    }

    /**
     * Determine if caching should be used.
     *
     * @return bool
     */
    protected function shouldUseCache(): bool
    {
        return $this->globalConfig['cache']['enabled'] ?? false;
    }

    /**
     * Get the cache key for the token.
     *
     * @return string
     */
    protected function getCacheKey(): string
    {
        return 'lineworks_access_token_' . md5($this->config['client_id']);
    }

    /**
     * Cache the token.
     *
     * @param string $token
     * @return void
     */
    protected function cacheToken(string $token): void
    {
        $store = $this->globalConfig['cache']['store'] ?? null;
        $ttl = $this->globalConfig['cache']['ttl'] ?? 30; // Default: 30 minutes

        if ($store) {
            Cache::store($store)->put($this->getCacheKey(), $token, $ttl * 60);
        } else {
            Cache::put($this->getCacheKey(), $token, $ttl * 60);
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
            call_user_func($this->loggerCallback, $level, '[AccessTokenManager] ' . $message, $context);
            return;
        }

        // Laravelのロギングが有効な場合はそれを使用
        if ($this->isLoggingEnabled()) {
            $channel = $this->globalConfig['logging']['channel'] ?? null;
            $logger = $channel ? Log::channel($channel) : Log::stack(['single']);
            
            $logger->{$level}('[AccessTokenManager] ' . $message, $context);
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
} 