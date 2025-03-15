<?php
/**
 * 認証処理のデバッグ用スクリプト
 * 
 * LINE WORKS APIの認証処理をデバッグするためのスクリプトです。
 * 詳細なログを出力して、認証エラーの原因を特定します。
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

// シンプルなロガーを作成（デバッグレベルで詳細なログを出力）
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
    'api_base_url' => 'https://www.worksapis.com/v1.0',
    'cache' => [
        'enabled' => false,
    ],
    'logging' => [
        'enabled' => true, // ロギングを有効化
        'level' => 'debug', // デバッグレベルに設定
    ],
];

echo "=== 認証情報のデバッグ ===\n";
echo "サービスアカウント: " . $botConfig['service_account'] . "\n";
echo "クライアントID: " . $botConfig['client_id'] . "\n";
echo "ドメインID: " . $botConfig['domain_id'] . "\n";
echo "ボットID: " . $botConfig['bot_id'] . "\n";
echo "スコープ: " . $botConfig['scope'] . "\n";
echo "秘密鍵の長さ: " . strlen($botConfig['private_key']) . " バイト\n";
echo "秘密鍵の先頭部分: " . substr($botConfig['private_key'], 0, 40) . "...\n";
echo "\n";

try {
    echo "JWTトークンの生成を試みます...\n";
    
    // JWT生成器を作成
    $jwtGenerator = new Sumihiro\LineWorksClient\Auth\JwtTokenGenerator($botConfig, $globalConfig);
    
    // JWTトークンを生成
    $jwtToken = $jwtGenerator->generate();
    
    echo "JWTトークンの生成に成功しました。\n";
    echo "JWTトークンの長さ: " . strlen($jwtToken) . " バイト\n";
    echo "JWTトークンの先頭部分: " . substr($jwtToken, 0, 40) . "...\n";
    echo "\n";
    
    echo "アクセストークンの取得を試みます...\n";
    
    // HTTPクライアントを作成
    $client = new GuzzleHttp\Client([
        'timeout' => 30,
        'http_errors' => false,
    ]);
    
    // アクセストークンのリクエストを送信
    $response = $client->post('https://auth.worksmobile.com/oauth2/v2.0/token', [
        'form_params' => [
            'assertion' => $jwtToken,
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'client_id' => $botConfig['client_id'],
            'client_secret' => $botConfig['client_secret'],
            'scope' => $botConfig['scope'],
        ],
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
    ]);
    
    // レスポンスを解析
    $statusCode = $response->getStatusCode();
    $responseBody = (string) $response->getBody();
    $responseData = json_decode($responseBody, true);
    
    echo "ステータスコード: " . $statusCode . "\n";
    echo "レスポンスボディ: " . $responseBody . "\n";
    
    if (isset($responseData['access_token'])) {
        echo "アクセストークンの取得に成功しました。\n";
        echo "アクセストークン: " . substr($responseData['access_token'], 0, 20) . "...\n";
    } else {
        echo "アクセストークンの取得に失敗しました。\n";
        if (isset($responseData['error'])) {
            echo "エラー: " . $responseData['error'] . "\n";
        }
        if (isset($responseData['error_description'])) {
            echo "エラー詳細: " . $responseData['error_description'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "エラーが発生しました: " . $e->getMessage() . "\n";
    if ($e instanceof \Sumihiro\LineWorksClient\Exceptions\AuthenticationException) {
        if ($e->getResponseData()) {
            echo "レスポンスデータ: " . json_encode($e->getResponseData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "ステータスコード: " . $e->getStatusCode() . "\n";
    }
    echo "スタックトレース: \n" . $e->getTraceAsString() . "\n";
} 