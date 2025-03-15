<?php
/**
 * LINE WORKS API設定ファイルのサンプル
 * 
 * このファイルをコピーして config.php を作成し、実際の認証情報を設定してください。
 * config.php は .gitignore に追加して、リポジトリにコミットされないようにしてください。
 */

// 秘密鍵ファイルのパス
$privateKeyPath = __DIR__ . '/private.key';

// 秘密鍵ファイルが存在するか確認
if (!file_exists($privateKeyPath)) {
    throw new Exception('秘密鍵ファイルが見つかりません。examples/private.example.key をコピーして examples/private.key を作成し、実際の秘密鍵を設定してください。');
}

// 秘密鍵を読み込む
$privateKey = file_get_contents($privateKeyPath);

return [
    // 基本認証に必須のパラメータ
    'service_account' => 'your-service-account',
    'private_key' => $privateKey, // 秘密鍵ファイルから読み込む
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'domain_id' => 'your-domain-id',
    'scope' => 'bot', // スコープを指定（デフォルトは 'bot'）
    
    // Bot機能を使用する場合に必須のパラメータ
    'bot_id' => 'your-bot-id',
    'bot_secret' => 'your-bot-secret',
    
    // テスト用のパラメータ
    'test_account_id' => 'test-user@example.com', // テスト用のアカウントID
    'test_channel_id' => 'test-channel-id', // テスト用のチャンネルID
];
