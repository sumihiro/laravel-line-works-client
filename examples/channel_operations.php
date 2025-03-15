<?php
/**
 * チャンネル（トークルーム）関連の操作を行うサンプルスクリプト
 * 
 * 使用方法:
 * php examples/channel_operations.php [操作] [パラメータ...]
 * 
 * 操作:
 * - create: チャンネルを作成します
 *   例: php examples/channel_operations.php create "テストチャンネル" user1@example.com user2@example.com
 * 
 * - info: チャンネル情報を取得します
 *   例: php examples/channel_operations.php info channel-id
 * 
 * - members: チャンネルのメンバーリストを取得します
 *   例: php examples/channel_operations.php members channel-id
 * 
 * - leave: チャンネルから退室します
 *   例: php examples/channel_operations.php leave channel-id
 * 
 * - send: チャンネルにメッセージを送信します
 *   例: php examples/channel_operations.php send channel-id "こんにちは！"
 */

// 共通の初期化処理
require_once __DIR__ . '/bootstrap.php';

// コマンドライン引数の処理
$operation = $argv[1] ?? null;

if (!$operation) {
    showUsage();
    exit(1);
}

try {
    switch ($operation) {
        case 'create':
            createChannel();
            break;
            
        case 'info':
            getChannelInfo();
            break;
            
        case 'members':
            getChannelMembers();
            break;
            
        case 'leave':
            leaveChannel();
            break;
            
        case 'send':
            sendChannelMessage();
            break;
            
        default:
            echo "エラー: 不明な操作 '{$operation}' です。\n";
            showUsage();
            exit(1);
    }
} catch (Sumihiro\LineWorksClient\Exceptions\ApiException $e) {
    echo "APIエラー: " . $e->getMessage() . "\n";
    
    if ($e->getResponseData()) {
        displayResult('エラーレスポンス', $e->getResponseData());
    }
    
    echo "ステータスコード: " . $e->getStatusCode() . "\n";
    
    if ($e->getRequestUrl()) {
        echo "リクエストURL: " . $e->getRequestUrl() . "\n";
    }
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage() . "\n";
}

/**
 * 使用方法を表示
 */
function showUsage()
{
    echo "使用方法: php examples/channel_operations.php [操作] [パラメータ...]\n\n";
    echo "操作:\n";
    echo "- create: チャンネルを作成します\n";
    echo "  例: php examples/channel_operations.php create \"テストチャンネル\" user1@example.com user2@example.com\n\n";
    echo "- info: チャンネル情報を取得します\n";
    echo "  例: php examples/channel_operations.php info channel-id\n\n";
    echo "- members: チャンネルのメンバーリストを取得します\n";
    echo "  例: php examples/channel_operations.php members channel-id\n\n";
    echo "- leave: チャンネルから退室します\n";
    echo "  例: php examples/channel_operations.php leave channel-id\n\n";
    echo "- send: チャンネルにメッセージを送信します\n";
    echo "  例: php examples/channel_operations.php send channel-id \"こんにちは！\"\n";
}

/**
 * チャンネルを作成
 */
function createChannel()
{
    global $argv, $botClient;
    
    $title = $argv[2] ?? 'テストチャンネル';
    $accountIds = array_slice($argv, 3);
    
    if (empty($accountIds)) {
        echo "エラー: チャンネルに追加するアカウントIDが指定されていません。\n";
        echo "例: php examples/channel_operations.php create \"テストチャンネル\" user1@example.com user2@example.com\n";
        exit(1);
    }
    
    echo "チャンネル '{$title}' を作成します...\n";
    echo "メンバー: " . implode(', ', $accountIds) . "\n";
    
    $response = $botClient->channel()->create($accountIds, $title);
    
    echo "=== 生のAPIレスポンス ===\n";
    echo json_encode($response->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    displayResult('チャンネル作成結果', [
        'channelId' => $response->getChannelId(),
        'success' => $response->isSuccess(),
    ]);
}

/**
 * チャンネル情報を取得
 */
function getChannelInfo()
{
    global $argv, $botClient, $botConfig;
    
    $channelId = $argv[2] ?? $botConfig['test_channel_id'] ?? null;
    
    if (!$channelId) {
        echo "エラー: チャンネルIDが指定されていません。\n";
        echo "例: php examples/channel_operations.php info channel-id\n";
        exit(1);
    }
    
    echo "チャンネルID: {$channelId} の情報を取得します...\n";
    
    $response = $botClient->channel()->info($channelId);
    
    echo "=== 生のAPIレスポンス ===\n";
    echo json_encode($response->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    displayResult('チャンネル情報', [
        'channelId' => $response->getChannelId(),
        'title' => $response->getTitle(),
        'type' => $response->getType(),
        'domainId' => $response->getDomainId(),
        'createdTime' => $response->getCreatedTime(),
        'status' => $response->getStatus(),
    ]);
}

/**
 * チャンネルのメンバーリストを取得
 */
function getChannelMembers()
{
    global $argv, $botClient, $botConfig;
    
    $channelId = $argv[2] ?? $botConfig['test_channel_id'] ?? null;
    
    if (!$channelId) {
        echo "エラー: チャンネルIDが指定されていません。\n";
        echo "例: php examples/channel_operations.php members channel-id\n";
        exit(1);
    }
    
    echo "チャンネルID: {$channelId} のメンバーリストを取得します...\n";
    
    $response = $botClient->channel()->members($channelId);
    
    echo "=== 生のAPIレスポンス ===\n";
    echo json_encode($response->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    displayResult('チャンネルメンバー', [
        'members' => $response->getMembers(),
        'memberCount' => $response->getMemberCount(),
        'nextCursor' => $response->getNextCursor(),
    ]);
}

/**
 * チャンネルから退室
 */
function leaveChannel()
{
    global $argv, $botClient, $botConfig;
    
    $channelId = $argv[2] ?? $botConfig['test_channel_id'] ?? null;
    
    if (!$channelId) {
        echo "エラー: チャンネルIDが指定されていません。\n";
        echo "例: php examples/channel_operations.php leave channel-id\n";
        exit(1);
    }
    
    echo "チャンネルID: {$channelId} から退室します...\n";
    
    $result = $botClient->channel()->leave($channelId);
    
    displayResult('チャンネル退室結果', [
        'success' => $result,
    ]);
}

/**
 * チャンネルにメッセージを送信
 */
function sendChannelMessage()
{
    global $argv, $botClient, $botConfig;
    
    $channelId = $argv[2] ?? $botConfig['test_channel_id'] ?? null;
    $message = $argv[3] ?? 'チャンネルへのテストメッセージ';
    
    if (!$channelId) {
        echo "エラー: チャンネルIDが指定されていません。\n";
        echo "例: php examples/channel_operations.php send channel-id \"こんにちは！\"\n";
        exit(1);
    }
    
    echo "チャンネルID: {$channelId} にメッセージを送信します...\n";
    
    $response = $botClient->channel()->sendMessage($channelId, [
        'content' => [
            'type' => 'text',
            'text' => $message,
        ],
    ]);
    
    echo "=== 生のAPIレスポンス ===\n";
    echo json_encode($response->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    displayResult('チャンネルメッセージ送信結果', [
        'success' => $response->isSuccess(),
        'messageId' => $response->getMessageId(),
    ]);
} 