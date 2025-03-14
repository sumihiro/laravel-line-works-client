<?php

namespace Sumihiro\LineWorksClient\Facades;

use Illuminate\Support\Facades\Facade;
use Sumihiro\LineWorksClient\LineWorksManager;

/**
 * @method static \Sumihiro\LineWorksClient\LineWorksClient bot(?string $name = null)
 * @method static \Sumihiro\LineWorksClient\Bot\BotClient botClient(?string $name = null)
 * @method static string getDefaultBot()
 * @method static array<string, mixed> get(string $endpoint, array<string, mixed> $query = [], array<string, string> $headers = [])
 * @method static array<string, mixed> post(string $endpoint, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method static array<string, mixed> put(string $endpoint, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method static array<string, mixed> delete(string $endpoint, array<string, mixed> $query = [], array<string, string> $headers = [])
 *
 * @see \Sumihiro\LineWorksClient\LineWorksManager
 */
class LineWorks extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'lineworks';
    }
} 