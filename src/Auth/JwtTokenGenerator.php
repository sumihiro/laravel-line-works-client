<?php

namespace Sumihiro\LineWorksClient\Auth;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Sumihiro\LineWorksClient\Exceptions\AuthenticationException;
use Sumihiro\LineWorksClient\Exceptions\ConfigurationException;

class JwtTokenGenerator
{
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
     * Create a new JWT token generator instance.
     *
     * @param array<string, mixed> $config
     * @param array<string, mixed> $globalConfig
     * @return void
     */
    public function __construct(array $config, array $globalConfig)
    {
        $this->config = $config;
        $this->globalConfig = $globalConfig;
    }

    /**
     * Generate a JWT token.
     *
     * @return string
     * @throws \Sumihiro\LineWorksClient\Exceptions\AuthenticationException
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    public function generate(): string
    {
        // Check if caching is enabled and token exists in cache
        if ($this->shouldUseCache()) {
            $cacheKey = $this->getCacheKey();
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
        }

        // Validate required configuration
        $this->validateConfig();

        // Generate a new token
        $token = $this->generateToken();

        // Cache the token if caching is enabled
        if ($this->shouldUseCache()) {
            $this->cacheToken($token);
        }

        return $token;
    }

    /**
     * Generate a JWT token.
     *
     * @return string
     * @throws \Sumihiro\LineWorksClient\Exceptions\AuthenticationException
     */
    protected function generateToken(): string
    {
        try {
            $privateKey = $this->getPrivateKey();
            $now = time();
            
            // LINE WORKSのドキュメントに従ったJWTペイロード（必須クレームのみ）
            $payload = [
                'iss' => $this->config['client_id'], // サービスアカウントのクライアントID
                'sub' => $this->config['service_account'], // サービスアカウントのメールアドレス
                'iat' => $now, // 発行時刻
                'exp' => $now + 3600, // 有効期限（1時間後）
            ];

            return JWT::encode($payload, $privateKey, 'RS256');
        } catch (\Exception $e) {
            throw new AuthenticationException(
                'Failed to generate JWT token: ' . $e->getMessage(),
                0,
                null,
                null,
                $e
            );
        }
    }

    /**
     * Get the private key.
     *
     * @return string
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    protected function getPrivateKey(): string
    {
        $privateKey = $this->config['private_key'];

        // If the private key is a file path, read the file
        if (file_exists($privateKey) && is_readable($privateKey)) {
            $privateKey = file_get_contents($privateKey);
            if ($privateKey === false) {
                throw new ConfigurationException('Failed to read private key file');
            }
        }

        // Ensure the private key is in the correct format
        if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') === false) {
            $privateKey = "-----BEGIN PRIVATE KEY-----\n" . 
                          chunk_split($privateKey, 64, "\n") . 
                          "-----END PRIVATE KEY-----";
        }

        return $privateKey;
    }

    /**
     * Validate the configuration.
     *
     * @return void
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    protected function validateConfig(): void
    {
        if (empty($this->config['service_account'])) {
            throw new ConfigurationException('Service account is required');
        }

        if (empty($this->config['private_key'])) {
            throw new ConfigurationException('Private key is required');
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
        return 'lineworks_jwt_token_' . md5($this->config['service_account']);
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
} 