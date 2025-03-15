<?php

namespace Sumihiro\LineWorksClient\Examples;

/**
 * サンプルスクリプト用の簡易的なロガークラス
 */
class SimpleLogger
{
    /**
     * ログレベル
     */
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';

    /**
     * ログが有効かどうか
     *
     * @var bool
     */
    protected bool $enabled;

    /**
     * ログレベル
     *
     * @var string
     */
    protected string $level;

    /**
     * コンストラクタ
     *
     * @param bool $enabled ログが有効かどうか
     * @param string $level ログレベル
     */
    public function __construct(bool $enabled = true, string $level = self::DEBUG)
    {
        $this->enabled = $enabled;
        $this->level = $level;
    }

    /**
     * デバッグログを出力
     *
     * @param string $message メッセージ
     * @param array<string, mixed> $context コンテキスト
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        if ($this->enabled && $this->level === self::DEBUG) {
            $this->log(self::DEBUG, $message, $context);
        }
    }

    /**
     * 情報ログを出力
     *
     * @param string $message メッセージ
     * @param array<string, mixed> $context コンテキスト
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        if ($this->enabled && in_array($this->level, [self::DEBUG, self::INFO])) {
            $this->log(self::INFO, $message, $context);
        }
    }

    /**
     * 警告ログを出力
     *
     * @param string $message メッセージ
     * @param array<string, mixed> $context コンテキスト
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        if ($this->enabled && in_array($this->level, [self::DEBUG, self::INFO, self::WARNING])) {
            $this->log(self::WARNING, $message, $context);
        }
    }

    /**
     * エラーログを出力
     *
     * @param string $message メッセージ
     * @param array<string, mixed> $context コンテキスト
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        if ($this->enabled) {
            $this->log(self::ERROR, $message, $context);
        }
    }

    /**
     * ログを出力
     *
     * @param string $level ログレベル
     * @param string $message メッセージ
     * @param array<string, mixed> $context コンテキスト
     * @return void
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        echo "[{$timestamp}] [{$levelUpper}] {$message}{$contextStr}" . PHP_EOL;
    }
} 