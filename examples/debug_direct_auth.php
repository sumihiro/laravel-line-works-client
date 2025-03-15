<?php
/**
 * LINE WORKSのドキュメントに従って直接アクセストークンを取得するスクリプト
 * 
 * このスクリプトは、LINE WORKSのドキュメントに記載されている通りのリクエストを直接送信します。
 * https://developers.worksmobile.com/jp/docs/auth-jwt
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
$config = require $configFile;

// 必要な認証情報を取得
$serviceAccount = $config['service_account'];
$privateKey = $config['private_key'];
$clientId = $config['client_id'];
$clientSecret = $config['client_secret'];
$domainId = $config['domain_id'];
$scope = $config['scope'] ?? 'bot';

echo "=== 認証情報 ===\n";
echo "サービスアカウント: " . $serviceAccount . "\n";
echo "クライアントID: " . $clientId . "\n";
echo "ドメインID: " . $domainId . "\n";
echo "スコープ: " . $scope . "\n";
echo "秘密鍵の長さ: " . strlen($privateKey) . " バイト\n";
echo "秘密鍵の先頭部分: " . substr($privateKey, 0, 40) . "...\n";
echo "\n";

try {
    // JWTトークンを生成
    echo "JWTトークンを生成します...\n";
    
    // 現在時刻を取得
    $now = time();
    
    // JWTペイロードを作成
    $payload = [
        'iss' => $clientId, // サービスアカウントのクライアントID
        'sub' => $serviceAccount, // サービスアカウントのメールアドレス
        'iat' => $now, // 発行時刻
        'exp' => $now + 3600, // 有効期限（1時間後）
    ];
    
    // JWTトークンを生成
    $jwtToken = Firebase\JWT\JWT::encode($payload, $privateKey, 'RS256');
    
    echo "JWTトークンの生成に成功しました。\n";
    echo "JWTトークンの長さ: " . strlen($jwtToken) . " バイト\n";
    echo "JWTトークンの先頭部分: " . substr($jwtToken, 0, 40) . "...\n";
    echo "\n";
    
    // アクセストークンを取得
    echo "アクセストークンを取得します...\n";
    
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
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
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
        echo "トークンタイプ: " . ($responseData['token_type'] ?? 'なし') . "\n";
        echo "有効期限: " . ($responseData['expires_in'] ?? 'なし') . " 秒\n";
        echo "スコープ: " . ($responseData['scope'] ?? 'なし') . "\n";
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
    echo "スタックトレース: \n" . $e->getTraceAsString() . "\n";
} 