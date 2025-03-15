<?php
/**
 * メッセージ送信のサンプルスクリプト
 * 
 * 使用方法:
 * php examples/send_message.php [アカウントID] [メッセージ]
 * 
 * 例:
 * php examples/send_message.php user@example.com "こんにちは！"
 */

// 共通の初期化処理
require_once __DIR__ . '/bootstrap.php';

// コマンドライン引数の処理
$accountId = $argv[1] ?? $botConfig['test_account_id'] ?? null;
$message = $argv[2] ?? 'テストメッセージ';

if (!$accountId) {
    echo "エラー: アカウントIDが指定されていません。\n";
    echo "使用方法: php examples/send_message.php [アカウントID] [メッセージ]\n";
    exit(1);
}

try {
    echo "アカウントID: {$accountId} にメッセージを送信します...\n";
    
    // テキストメッセージの送信
    $response = $botClient->message()->sendText($accountId, $message);
    
    displayResult('メッセージ送信結果', [
        'success' => $response->isSuccess(),
        'messageId' => $response->getMessageId(),
    ]);
    
    // 構造化メッセージの送信例
    $structuredMessage = [
        'content' => [
            'type' => 'text',
            'text' => '構造化メッセージのテスト',
        ],
    ];
    
    $response = $botClient->message()->sendMessage($accountId, $structuredMessage);
    
    displayResult('構造化メッセージ送信結果', [
        'success' => $response->isSuccess(),
        'messageId' => $response->getMessageId(),
    ]);
    
} catch (Sumihiro\LineWorksClient\Exceptions\ApiException $e) {
    echo "APIエラー: " . $e->getMessage() . "\n";
    
    if ($e->getResponseData()) {
        displayResult('エラーレスポンス', $e->getResponseData());
    }
    
    echo "ステータスコード: " . $e->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage() . "\n";
} 