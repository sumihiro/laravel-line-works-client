<?php
/**
 * サンプルスクリプト共通の初期化処理
 */

// オートローダーの読み込み
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/SimpleLogger.php';

// 設定ファイルの読み込み
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    echo "エラー: config.php ファイルが見つかりません。\n";
    echo "examples/config.example.php をコピーして examples/config.php を作成し、実際の認証情報を設定してください。\n";
    exit(1);
}

// 設定の読み込み
$botConfig = require $configFile;

// シンプルなロガーを作成
$logger = new Sumihiro\LineWorksClient\Examples\SimpleLogger(
    true, // ログを有効にする
    'debug' // ログレベル
);

// ロガーコールバック関数
$loggerCallback = function ($level, $message, $context = []) use ($logger) {
    switch ($level) {
        case 'debug':
            $logger->debug($message, $context);
            break;
        case 'info':
            $logger->info($message, $context);
            break;
        case 'warning':
            $logger->warning($message, $context);
            break;
        case 'error':
            $logger->error($message, $context);
            break;
    }
};

// グローバル設定
$globalConfig = [
    'api_base_url' => 'https://www.worksapis.com/v1.0/',
    'cache' => [
        'enabled' => false,
    ],
    'logging' => [
        'enabled' => false, // Laravelのロギングは無効化
    ],
];

// LineWorksClientとBotClientのインスタンスを作成
$client = new Sumihiro\LineWorksClient\LineWorksClient('default', $botConfig, $globalConfig);

// カスタムロガーを設定
$client->setLogger($loggerCallback);

// AccessTokenManagerにもカスタムロガーを設定
$accessTokenManager = $client->getAccessTokenManager();
if ($accessTokenManager) {
    $accessTokenManager->setLogger($loggerCallback);
}

$botClient = new Sumihiro\LineWorksClient\Bot\BotClient($client);

/**
 * 結果を表示する関数
 *
 * @param string $title タイトル
 * @param mixed $data 表示するデータ
 * @return void
 */
function displayResult($title, $data)
{
    echo "=== {$title} ===\n";
    
    if (is_object($data) && method_exists($data, 'toArray')) {
        $data = $data->toArray();
    }
    
    if (is_array($data)) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo $data . "\n";
    }
    
    echo "\n";
} 