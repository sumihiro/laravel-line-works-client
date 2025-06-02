# トーク内容ダウンロード機能

LINE WORKS Bot APIのトーク内容ダウンロード機能を使用してトークルームのメッセージ履歴をCSV形式でダウンロードし、構造化されたデータとして処理する方法を説明します。

## 概要

この機能は302リダイレクトを使用した2段階のプロセスでメッセージ内容をダウンロードし、CSVデータを自動的に解析して使いやすい形式で提供します：

1. **Step 1**: `/monitoring/message-contents/download`エンドポイントにGETリクエストを送信し、302リダイレクトでダウンロードURLを取得
2. **Step 2**: 取得したURLにアクセスして実際のCSVコンテンツをダウンロード
3. **Step 3**: CSVデータを自動的に解析し、構造化されたメッセージ配列として提供

## テスト

この機能のテストは以下のコマンドで実行できます：

```bash
# 特定のテストクラスを実行
./vendor/bin/phpunit tests/Unit/Bot/Monitoring/MessageContentsClientTest.php
./vendor/bin/phpunit tests/Unit/Bot/Monitoring/MessageContentsResponseTest.php

# 全てのテストを実行
./vendor/bin/phpunit

# カバレッジレポート付きで実行
./vendor/bin/phpunit --coverage-html coverage
```

**注意**: テストにはMockeryが必要です。まだインストールしていない場合は以下でインストールしてください：

```bash
composer require --dev mockery/mockery
```

## 注意事項

- **スコープ**: `monitoring.read` が必要です
- **権限**: 対象ドメインの最高管理者、副管理者または Service Account のみ利用可能
- **並行実行禁止**: このAPIは並行して呼び出さないでください。前回のダウンロードが完了してから次のリクエストを送信してください
- **日付制限**: 開始日から終了日までの取得期間は最長31日まで指定可能
- **URL有効期限**: 取得したダウンロードURLは一定期間を過ぎると無効になります

## 基本的な使用方法

```php
use Sumihiro\LineWorksClient\LineWorksManager;

// LINE WORKS クライアントを取得
$lineWorks = app(LineWorksManager::class);
$client = $lineWorks->bot('default');

// 基本的なダウンロード（必須パラメータのみ）
$response = $client->monitoring()->download(
    startTime: '2025-05-01T00:00:00+09:00',
    endTime: '2025-05-31T23:59:59+09:00',
    language: 'ja_JP'
);

// レスポンスを確認
if ($response->isSuccess()) {
    $downloadUrl = $response->getDownloadUrl();
    $csvContent = $response->getCsvContent(); // 生のCSVデータ
    $messages = $response->getMessages();     // 解析済みメッセージ配列
    $metadata = $response->getMetadata();
    
    echo "Download URL: " . $downloadUrl . "\n";
    echo "Total messages: " . $response->getMessageCount() . "\n";
    
    // 各メッセージを処理
    foreach ($messages as $message) {
        echo "日時: " . $message['datetime'] . "\n";
        echo "送信者: " . $message['sender'] . "\n";
        echo "受信者: " . $message['receiver'] . "\n";
        echo "チャンネル: " . $message['channel_id'] . "\n";
        echo "メッセージ: " . $message['message'] . "\n";
        echo "---\n";
    }
}
```

## メッセージデータの取得と処理

### 全メッセージの取得

```php
// 全てのメッセージを取得（CSVから自動解析）
$allMessages = $response->getMessages();

// メッセージ数を確認
$totalCount = $response->getMessageCount();
echo "Total messages: {$totalCount}\n";

// 各メッセージの構造
foreach ($allMessages as $index => $message) {
    echo "Message {$index}:\n";
    echo "  日時: " . $message['datetime'] . "\n";       // 2025-05-30T18:24:53+09:00
    echo "  送信者: " . $message['sender'] . "\n";       // [Bot]test または user@example.com
    echo "  受信者: " . $message['receiver'] . "\n";     // 受信者
    echo "  チャンネル: " . $message['channel_id'] . "\n"; // channel123
    echo "  メッセージ: " . $message['message'] . "\n";   // メッセージ内容
}
```

### フィルタリング機能

```php
// 1. 送信者で絞り込み
$botMessages = $response->getMessagesBySender('[Bot]test');
$userMessages = $response->getMessagesBySender('user@example.com');

echo "Bot messages: " . count($botMessages) . "\n";
echo "User messages: " . count($userMessages) . "\n";

// 2. チャンネルで絞り込み
$channelMessages = $response->getMessagesByChannel('channel123');
echo "Channel 123 messages: " . count($channelMessages) . "\n";

// 3. 日付範囲で絞り込み（Y-m-d形式）
$todayMessages = $response->getMessagesByDateRange('2025-05-30', '2025-05-30');
$weekMessages = $response->getMessagesByDateRange('2025-05-24', '2025-05-30');

echo "Today's messages: " . count($todayMessages) . "\n";
echo "This week's messages: " . count($weekMessages) . "\n";

// 4. Bot/ユーザーメッセージを分離
$botOnly = $response->getBotMessages();      // [Bot]で始まる送信者のメッセージ
$userOnly = $response->getUserMessages();    // [Bot]で始まらない送信者のメッセージ

echo "Bot messages: " . count($botOnly) . "\n";
echo "User messages: " . count($userOnly) . "\n";
```

### 配列形式での一括取得

```php
// 全データを配列形式で取得
$data = $response->toArray();

echo "Download URL: " . $data['downloadUrl'] . "\n";
echo "Message count: " . $data['messageCount'] . "\n";
echo "Metadata: " . json_encode($data['metadata']) . "\n";

// メッセージを処理
foreach ($data['messages'] as $message) {
    // 処理ロジック
}
```

## 詳細なパラメータ指定

```php
// 全てのオプションパラメータを指定
$response = $client->monitoring()->download(
    startTime: '2025-05-01T00:00:00+09:00',        // 開始日時（YYYY-MM-DDThh:mm:ssTZD形式）
    endTime: '2025-05-31T23:59:59+09:00',          // 終了日時（YYYY-MM-DDThh:mm:ssTZD形式）
    language: 'ja_JP',                             // CSV言語（ja_JP, ko_KR, zh_CN, zh_TW, en_US）
    botMessageFilterType: 'include',               // Botメッセージフィルタ（include, exclude, only）
    domainId: 10000001,                            // ドメインID（グループ企業で他ドメインの取得時）
    rogerMessageFilterType: 'include'              // ラジャーメッセージフィルタ（include, exclude, only）
);
```

## ダウンロードURLのみを取得

実際のダウンロードを行わず、ダウンロードURLのみを取得することも可能です：

```php
$downloadUrl = $client->monitoring()->getDownloadUrlOnly(
    startTime: '2025-05-01T00:00:00+09:00',
    endTime: '2025-05-31T23:59:59+09:00',
    language: 'ja_JP'
);

echo "Download URL: " . $downloadUrl . "\n";

// 後で別途ダウンロードする場合
$csvContent = file_get_contents($downloadUrl);
file_put_contents('messages.csv', $csvContent);
```

## パラメータの詳細

### 必須パラメータ

- **startTime**: 取得開始日時。YYYY-MM-DDThh:mm:ssTZD形式で指定
  - 例: `2025-05-01T00:00:00+09:00` (JST), `2025-05-01T00:00:00Z` (UTC)
- **endTime**: 取得終了日時。YYYY-MM-DDThh:mm:ssTZD形式で指定
  - 例: `2025-05-31T23:59:59+09:00` (JST), `2025-05-31T23:59:59Z` (UTC)

### オプションパラメータ

- **language**: CSVファイルの言語（デフォルト: `en_US`）
  - `ja_JP`: 日本語
  - `ko_KR`: 韓国語
  - `zh_CN`: 中国語（簡体字）
  - `zh_TW`: 中国語（繁体字）
  - `en_US`: 英語

- **botMessageFilterType**: Botトークメッセージフィルタ（デフォルト: `include`）
  - `include`: Botメッセージを含める
  - `exclude`: Botメッセージを除く
  - `only`: Botメッセージのみ

- **rogerMessageFilterType**: ラジャーのトークメッセージフィルタ（デフォルト: `include`）
  - `include`: ラジャーメッセージを含める
  - `exclude`: ラジャーメッセージを除く
  - `only`: ラジャーメッセージのみ

- **domainId**: ドメインID。グループ企業で別のドメインのログを取得する場合に指定

## CSVデータの安全な処理

このライブラリは`SplFileObject`を使用してCSVデータを安全に解析します。以下のCSV特殊ケースに対応しています：

```php
// フィールド内改行を含むメッセージ
$messages = $response->getMessages();
foreach ($messages as $message) {
    // メッセージ内に改行が含まれていても正しく処理される
    echo "Message:\n" . $message['message'] . "\n";
}

// エスケープされたクォートやカンマを含むデータも正しく処理
// 例: "He said ""Hello"" to me" や "One, Two, Three"
```

## レスポンスの処理

### メッセージ統計の取得

```php
$response = $client->monitoring()->download(
    startTime: '2025-05-01T00:00:00+09:00',
    endTime: '2025-05-31T23:59:59+09:00',
    language: 'ja_JP'
);

if ($response->isSuccess()) {
    $totalMessages = $response->getMessageCount();
    $botMessages = count($response->getBotMessages());
    $userMessages = count($response->getUserMessages());
    
    echo "=== メッセージ統計 ===\n";
    echo "総メッセージ数: {$totalMessages}\n";
    echo "Botメッセージ数: {$botMessages}\n";
    echo "ユーザーメッセージ数: {$userMessages}\n";
    
    // 送信者別統計
    $senderStats = [];
    foreach ($response->getMessages() as $message) {
        $sender = $message['sender'];
        $senderStats[$sender] = ($senderStats[$sender] ?? 0) + 1;
    }
    
    echo "\n=== 送信者別統計 ===\n";
    arsort($senderStats);
    foreach ($senderStats as $sender => $count) {
        echo "{$sender}: {$count}件\n";
    }
}
```

### CSVファイルとして保存

```php
if ($response->isSuccess()) {
    // 生のCSVデータを保存
    $csvContent = $response->getCsvContent();
    file_put_contents('messages.csv', $csvContent);
    
    // 構造化データをJSONとして保存
    $jsonData = json_encode($response->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents('messages.json', $jsonData);
    
    echo "Data saved to messages.csv and messages.json\n";
}
```

## エラーハンドリング

```php
use Sumihiro\LineWorksClient\Exceptions\ApiException;

try {
    $response = $client->monitoring()->download(
        startTime: '2025-05-01T00:00:00+09:00',
        endTime: '2025-05-31T23:59:59+09:00'
    );
    
    if ($response->isSuccess()) {
        // 成功時の処理
        $messages = $response->getMessages();
        echo "Downloaded " . count($messages) . " messages successfully.\n";
    } else {
        echo "Download failed: " . json_encode($response->toArray()) . "\n";
    }
} catch (ApiException $e) {
    // エラー情報を取得
    $statusCode = $e->getStatusCode();
    $responseData = $e->getResponseData();
    
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $statusCode . "\n";
    
    // パラメータ検証エラーの場合
    if (isset($responseData['provided_language'])) {
        echo "Invalid language: " . $responseData['provided_language'] . "\n";
        echo "Allowed languages: " . implode(', ', $responseData['allowed_languages']) . "\n";
    }
}
```

## 実用的な使用例

### 前日のメッセージをバックアップ

```php
function backupYesterdayMessages(): bool {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $startTime = $yesterday . 'T00:00:00+09:00';
    $endTime = $yesterday . 'T23:59:59+09:00';
    
    $lineWorks = app(LineWorksManager::class);
    $client = $lineWorks->bot('default');
    
    try {
        $response = $client->monitoring()->download(
            startTime: $startTime,
            endTime: $endTime,
            language: 'ja_JP'
        );
        
        if ($response->isSuccess()) {
            $backupDir = "backup/" . $yesterday;
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // CSVファイルとして保存
            file_put_contents("{$backupDir}/messages.csv", $response->getCsvContent());
            
            // 構造化データも保存
            file_put_contents("{$backupDir}/messages.json", 
                json_encode($response->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            echo "Yesterday's " . $response->getMessageCount() . " messages backed up to: {$backupDir}\n";
            return true;
        }
    } catch (ApiException $e) {
        echo "Backup failed: " . $e->getMessage() . "\n";
    }
    
    return false;
}

// 使用例
backupYesterdayMessages();
```

### 特定期間のBotメッセージのみを抽出・分析

```php
function analyzeBotMessages(string $startDate, string $endDate): void {
    $startTime = $startDate . 'T00:00:00+09:00';
    $endTime = $endDate . 'T23:59:59+09:00';
    
    $lineWorks = app(LineWorksManager::class);
    $client = $lineWorks->bot('default');
    
    try {
        $response = $client->monitoring()->download(
            startTime: $startTime,
            endTime: $endTime,
            language: 'ja_JP',
            botMessageFilterType: 'only'  // Botメッセージのみ
        );
        
        if ($response->isSuccess()) {
            $botMessages = $response->getMessages();
            
            echo "=== Bot メッセージ分析 ({$startDate} - {$endDate}) ===\n";
            echo "総Botメッセージ数: " . count($botMessages) . "\n";
            
            // Bot別統計
            $botStats = [];
            foreach ($botMessages as $message) {
                $bot = $message['sender'];
                $botStats[$bot] = ($botStats[$bot] ?? 0) + 1;
            }
            
            echo "\n=== Bot別メッセージ数 ===\n";
            arsort($botStats);
            foreach ($botStats as $bot => $count) {
                echo "{$bot}: {$count}件\n";
            }
            
            // チャンネル別統計
            $channelStats = [];
            foreach ($botMessages as $message) {
                $channel = $message['channel_id'];
                $channelStats[$channel] = ($channelStats[$channel] ?? 0) + 1;
            }
            
            echo "\n=== チャンネル別メッセージ数 ===\n";
            arsort($channelStats);
            foreach ($channelStats as $channel => $count) {
                echo "{$channel}: {$count}件\n";
            }
            
            // データを保存
            $filename = "bot_analysis_{$startDate}_to_{$endDate}.json";
            $analysisData = [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'total_bot_messages' => count($botMessages),
                'bot_stats' => $botStats,
                'channel_stats' => $channelStats,
                'messages' => $botMessages
            ];
            
            file_put_contents($filename, json_encode($analysisData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo "\nAnalysis saved to: {$filename}\n";
        }
    } catch (ApiException $e) {
        echo "Analysis failed: " . $e->getMessage() . "\n";
    }
}

// 使用例
analyzeBotMessages('2025-05-01', '2025-05-31');
```

### チャンネル別メッセージ分析

```php
function analyzeChannelActivity(string $startDate, string $endDate): array {
    $startTime = $startDate . 'T00:00:00+09:00';
    $endTime = $endDate . 'T23:59:59+09:00';
    
    $lineWorks = app(LineWorksManager::class);
    $client = $lineWorks->bot('default');
    
    try {
        $response = $client->monitoring()->download(
            startTime: $startTime,
            endTime: $endTime,
            language: 'ja_JP'
        );
        
        if ($response->isSuccess()) {
            $allMessages = $response->getMessages();
            $channelAnalysis = [];
            
            // 各チャンネルのメッセージを取得
            $channels = array_unique(array_column($allMessages, 'channel_id'));
            
            foreach ($channels as $channelId) {
                $channelMessages = $response->getMessagesByChannel($channelId);
                $botMessages = array_filter($channelMessages, fn($msg) => str_starts_with($msg['sender'], '[Bot]'));
                $userMessages = array_filter($channelMessages, fn($msg) => !str_starts_with($msg['sender'], '[Bot]'));
                
                $channelAnalysis[$channelId] = [
                    'total_messages' => count($channelMessages),
                    'bot_messages' => count($botMessages),
                    'user_messages' => count($userMessages),
                    'first_message' => reset($channelMessages)['datetime'] ?? null,
                    'last_message' => end($channelMessages)['datetime'] ?? null,
                ];
            }
            
            // 結果を表示
            echo "=== チャンネル別活動分析 ({$startDate} - {$endDate}) ===\n";
            foreach ($channelAnalysis as $channelId => $stats) {
                echo "\nChannel: {$channelId}\n";
                echo "  総メッセージ数: {$stats['total_messages']}\n";
                echo "  Botメッセージ: {$stats['bot_messages']}\n";
                echo "  ユーザーメッセージ: {$stats['user_messages']}\n";
                echo "  期間: {$stats['first_message']} - {$stats['last_message']}\n";
            }
            
            return $channelAnalysis;
        }
    } catch (ApiException $e) {
        echo "Channel analysis failed: " . $e->getMessage() . "\n";
    }
    
    return [];
}

// 使用例
$analysis = analyzeChannelActivity('2025-05-01', '2025-05-31');
```

### 日別メッセージ傾向分析

```php
function dailyMessageTrends(string $startDate, string $endDate): void {
    $startTime = $startDate . 'T00:00:00+09:00';
    $endTime = $endDate . 'T23:59:59+09:00';
    
    $lineWorks = app(LineWorksManager::class);
    $client = $lineWorks->bot('default');
    
    try {
        $response = $client->monitoring()->download(
            startTime: $startTime,
            endTime: $endTime,
            language: 'ja_JP'
        );
        
        if ($response->isSuccess()) {
            $allMessages = $response->getMessages();
            $dailyStats = [];
            
            // 日別にメッセージを集計
            foreach ($allMessages as $message) {
                $date = date('Y-m-d', strtotime($message['datetime']));
                if (!isset($dailyStats[$date])) {
                    $dailyStats[$date] = ['total' => 0, 'bot' => 0, 'user' => 0];
                }
                
                $dailyStats[$date]['total']++;
                if (str_starts_with($message['sender'], '[Bot]')) {
                    $dailyStats[$date]['bot']++;
                } else {
                    $dailyStats[$date]['user']++;
                }
            }
            
            echo "=== 日別メッセージ傾向 ===\n";
            ksort($dailyStats);
            foreach ($dailyStats as $date => $stats) {
                echo "{$date}: 総計{$stats['total']}件 (Bot: {$stats['bot']}, User: {$stats['user']})\n";
            }
            
            // CSV形式で保存
            $csvOutput = "Date,Total,Bot,User\n";
            foreach ($dailyStats as $date => $stats) {
                $csvOutput .= "{$date},{$stats['total']},{$stats['bot']},{$stats['user']}\n";
            }
            file_put_contents("daily_trends_{$startDate}_to_{$endDate}.csv", $csvOutput);
            echo "\nTrends saved to daily_trends_{$startDate}_to_{$endDate}.csv\n";
        }
    } catch (ApiException $e) {
        echo "Trend analysis failed: " . $e->getMessage() . "\n";
    }
}

// 使用例
dailyMessageTrends('2025-05-01', '2025-05-31');
``` 