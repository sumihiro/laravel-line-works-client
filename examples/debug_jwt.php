<?php
/**
 * JWTトークンのデバッグ用スクリプト
 * 
 * JWTトークンの内容を確認するためのスクリプトです。
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

// グローバル設定
$globalConfig = [
    'api_base_url' => 'https://www.worksapis.com/v1.0',
    'cache' => [
        'enabled' => false,
    ],
    'logging' => [
        'enabled' => true,
        'level' => 'debug',
    ],
];

try {
    echo "JWTトークンの生成を試みます...\n";
    
    // JWT生成器を作成
    $jwtGenerator = new Sumihiro\LineWorksClient\Auth\JwtTokenGenerator($botConfig, $globalConfig);
    
    // JWTトークンを生成
    $jwtToken = $jwtGenerator->generate();
    
    echo "JWTトークンの生成に成功しました。\n";
    
    // JWTトークンの各部分を取得
    $tokenParts = explode('.', $jwtToken);
    if (count($tokenParts) !== 3) {
        echo "エラー: JWTトークンの形式が不正です。\n";
        exit(1);
    }
    
    // ヘッダーをデコード
    $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0])), true);
    
    // ペイロード（クレーム）をデコード
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);
    
    // ヘッダーを表示
    echo "\n=== JWTヘッダー ===\n";
    echo json_encode($header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    // ペイロードを表示
    echo "\n=== JWTペイロード ===\n";
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    // 重要なクレームを確認
    echo "\n=== 重要なクレームの確認 ===\n";
    
    // 発行者（iss）の確認
    echo "発行者（iss）: " . ($payload['iss'] ?? 'なし') . "\n";
    echo "期待される値: " . $botConfig['service_account'] . "\n";
    if (isset($payload['iss']) && $payload['iss'] === $botConfig['service_account']) {
        echo "✓ 発行者は正しいです。\n";
    } else {
        echo "✗ 発行者が正しくありません。\n";
    }
    
    // 対象者（aud）の確認
    echo "\n対象者（aud）: " . ($payload['aud'] ?? 'なし') . "\n";
    echo "期待される値: https://auth.worksmobile.com/oauth2/v2.0/token\n";
    if (isset($payload['aud']) && $payload['aud'] === 'https://auth.worksmobile.com/oauth2/v2.0/token') {
        echo "✓ 対象者は正しいです。\n";
    } else {
        echo "✗ 対象者が正しくありません。\n";
    }
    
    // 有効期限（exp）の確認
    if (isset($payload['exp'])) {
        $expTime = date('Y-m-d H:i:s', $payload['exp']);
        $now = date('Y-m-d H:i:s');
        echo "\n有効期限（exp）: " . $expTime . " (現在時刻: " . $now . ")\n";
        if ($payload['exp'] > time()) {
            echo "✓ 有効期限は未来の時刻です。\n";
        } else {
            echo "✗ 有効期限が過去の時刻です。\n";
        }
    } else {
        echo "\n有効期限（exp）: なし\n";
        echo "✗ 有効期限が設定されていません。\n";
    }
    
    // 発行時刻（iat）の確認
    if (isset($payload['iat'])) {
        $iatTime = date('Y-m-d H:i:s', $payload['iat']);
        echo "\n発行時刻（iat）: " . $iatTime . "\n";
    } else {
        echo "\n発行時刻（iat）: なし\n";
        echo "✗ 発行時刻が設定されていません。\n";
    }
    
    // スコープ（scope）の確認
    echo "\nスコープ（scope）: " . ($payload['scope'] ?? 'なし') . "\n";
    echo "期待される値: " . $botConfig['scope'] . "\n";
    if (isset($payload['scope']) && $payload['scope'] === $botConfig['scope']) {
        echo "✓ スコープは正しいです。\n";
    } else {
        echo "✗ スコープが正しくありません。\n";
    }
    
    // クライアントID（client_id）の確認
    if (isset($payload['client_id'])) {
        echo "\nクライアントID（client_id）: " . $payload['client_id'] . "\n";
        echo "期待される値: " . $botConfig['client_id'] . "\n";
        if ($payload['client_id'] === $botConfig['client_id']) {
            echo "✓ クライアントIDは正しいです。\n";
        } else {
            echo "✗ クライアントIDが正しくありません。\n";
        }
    } else {
        echo "\nクライアントID（client_id）: なし\n";
        echo "✗ クライアントIDが設定されていません。\n";
    }
    
} catch (Exception $e) {
    echo "エラーが発生しました: " . $e->getMessage() . "\n";
    echo "スタックトレース: \n" . $e->getTraceAsString() . "\n";
} 