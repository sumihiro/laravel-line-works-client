<?php

namespace Sumihiro\LineWorksClient;

use Illuminate\Contracts\Container\Container;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientFactoryInterface;
use Sumihiro\LineWorksClient\Contracts\Bot\BotClientInterface;
use Sumihiro\LineWorksClient\Exceptions\ConfigurationException;
use Sumihiro\LineWorksClient\Factories\BotClientFactory;

class LineWorksManager
{
    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected Container $container;

    /**
     * The configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * The active connections.
     *
     * @var array<string, \Sumihiro\LineWorksClient\LineWorksClient>
     */
    protected array $clients = [];

    /**
     * The bot client factory instance.
     *
     * @var \Sumihiro\LineWorksClient\Contracts\Bot\BotClientFactoryInterface
     */
    protected BotClientFactoryInterface $botClientFactory;

    /**
     * Create a new LINE WORKS manager instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param array<string, mixed> $config
     * @param \Sumihiro\LineWorksClient\Contracts\Bot\BotClientFactoryInterface|null $botClientFactory
     * @return void
     */
    public function __construct(Container $container, array $config, ?BotClientFactoryInterface $botClientFactory = null)
    {
        $this->container = $container;
        $this->config = $config;
        $this->botClientFactory = $botClientFactory ?? new BotClientFactory();
    }

    /**
     * Get a LINE WORKS client instance.
     *
     * @param string|null $name
     * @return \Sumihiro\LineWorksClient\LineWorksClient
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    public function bot(?string $name = null): LineWorksClient
    {
        $name = $name ?: $this->getDefaultBot();

        return $this->clients[$name] = $this->get($name);
    }

    /**
     * Get a LINE WORKS bot client instance.
     *
     * @param string|null $name
     * @return \Sumihiro\LineWorksClient\Contracts\Bot\BotClientInterface
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    public function botClient(?string $name = null): BotClientInterface
    {
        $client = $this->bot($name);
        
        return $this->botClientFactory->create($client);
    }

    /**
     * Get the default bot name.
     *
     * @return string
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    public function getDefaultBot(): string
    {
        $default = $this->config['default'] ?? 'default';

        if (empty($default)) {
            throw new ConfigurationException('Default bot is not configured');
        }

        return $default;
    }

    /**
     * Get the configuration for a bot.
     *
     * @param string $name
     * @return array<string, mixed>
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    protected function getBotConfig(string $name): array
    {
        $bots = $this->config['bots'] ?? [];

        if (!isset($bots[$name])) {
            throw new ConfigurationException("Bot [{$name}] is not configured");
        }

        return $bots[$name];
    }

    /**
     * Get a LINE WORKS client instance.
     *
     * @param string $name
     * @return \Sumihiro\LineWorksClient\LineWorksClient
     * @throws \Sumihiro\LineWorksClient\Exceptions\ConfigurationException
     */
    protected function get(string $name): LineWorksClient
    {
        if (isset($this->clients[$name])) {
            return $this->clients[$name];
        }

        $botConfig = $this->getBotConfig($name);
        $globalConfig = $this->getGlobalConfig();

        return $this->clients[$name] = new LineWorksClient($name, $botConfig, $globalConfig);
    }

    /**
     * Get the global configuration.
     *
     * @return array<string, mixed>
     */
    protected function getGlobalConfig(): array
    {
        return [
            'api_base_url' => $this->config['api_base_url'] ?? 'https://www.worksapis.com/v1.0',
            'cache' => $this->config['cache'] ?? [
                'enabled' => false,
            ],
            'logging' => $this->config['logging'] ?? [
                'enabled' => false,
            ],
        ];
    }

    /**
     * Set the bot client factory.
     * This method is primarily for testing purposes.
     *
     * @param \Sumihiro\LineWorksClient\Contracts\Bot\BotClientFactoryInterface $factory
     * @return void
     */
    public function setBotClientFactory(BotClientFactoryInterface $factory): void
    {
        $this->botClientFactory = $factory;
    }

    /**
     * Dynamically call the default client instance.
     *
     * @param string $method
     * @param array<int, mixed> $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->bot()->{$method}(...$parameters);
    }
} 