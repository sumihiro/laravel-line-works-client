# LINE WORKS API Client for Laravel

LINE WORKS APIをLaravelから簡単に利用するためのクライアントライブラリです。

## 機能

- LINE WORKS APIの認証（JWT）
- Bot APIの利用
- 複数のボット設定のサポート
- トークンのキャッシュ
- ログ機能

## インストール

Composerを使用してインストールできます：

```bash
composer require sumihiro/line-works-client
```

## 設定

### 設定ファイルの公開

以下のコマンドを実行して、設定ファイルを公開します：

```bash
php artisan vendor:publish --tag=lineworks-config
```

これにより、`config/lineworks.php`ファイルが作成されます。

### 環境変数の設定

`.env`ファイルに以下の設定を追加します：

```
# 基本認証に必須のパラメータ
LINEWORKS_SERVICE_ACCOUNT=your-service-account
LINEWORKS_PRIVATE_KEY=your-private-key
LINEWORKS_CLIENT_ID=your-client-id
LINEWORKS_CLIENT_SECRET=your-client-secret
LINEWORKS_DOMAIN_ID=your-domain-id
LINEWORKS_CONSUMER_KEY=your-consumer-key
LINEWORKS_SCOPE=bot

# Bot機能を使用する場合に必須のパラメータ
LINEWORKS_BOT_ID=your-bot-id
LINEWORKS_BOT_SECRET=your-bot-secret
```

## 使用方法

### ファサードの使用

```php
use Sumihiro\LineWorksClient\Facades\LineWorks;

// デフォルトのボットを使用
$response = LineWorks::botClient()->message()->sendText('user123', 'こんにちは！');

// 特定のボットを指定
$response = LineWorks::botClient('another_bot')->message()->sendText('user123', 'こんにちは！');

// スコープを指定してAPIを呼び出す
$response = LineWorks::withScope('user')->get('users/info');
```

### DIの使用

```php
use Sumihiro\LineWorksClient\LineWorksManager;

class MyController
{
    protected $lineWorks;

    public function __construct(LineWorksManager $lineWorks)
    {
        $this->lineWorks = $lineWorks;
    }

    public function sendMessage()
    {
        $response = $this->lineWorks->botClient()->message()->sendText('user123', 'こんにちは！');
        
        if ($response->isSuccess()) {
            return 'メッセージを送信しました！ ID: ' . $response->getMessageId();
        }
        
        return 'メッセージの送信に失敗しました。';
    }
    
    public function getUserInfo()
    {
        // スコープを指定してAPIを呼び出す
        $response = $this->lineWorks->withScope('user')->get('users/info');
        return json_decode($response->getBody(), true);
    }
}
```

### LineWorksClientを単体で使用する場合

Laravelのサービスコンテナやファサードを使わずに、直接LineWorksClientを使用することもできます。使用目的に応じて必要なパラメータが異なります：

#### 1. Bot機能を使用する場合（BotClient使用時）

```php
use GuzzleHttp\Client;
use Sumihiro\LineWorksClient\Auth\JwtTokenGenerator;
use Sumihiro\LineWorksClient\Auth\AccessTokenManager;
use Sumihiro\LineWorksClient\LineWorksClient;
use Sumihiro\LineWorksClient\Bot\BotClient;

// ボットの設定（Bot機能を使用する場合）
$botConfig = [
    // 基本認証に必須のパラメータ
    'service_account' => 'your-service-account',
    'private_key' => 'your-private-key',
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'domain_id' => 'your-domain-id',
    'consumer_key' => 'your-consumer-key',
    'scope' => 'bot', // スコープを指定（デフォルトは 'bot'）
    
    // Bot機能を使用する場合に必須のパラメータ
    'bot_id' => 'your-bot-id',
    'bot_secret' => 'your-bot-secret',
];

// グローバル設定
$globalConfig = [
    'api_base_url' => 'https://www.worksapis.com/v1.0',
    'cache' => [
        'enabled' => false,
    ],
    'logging' => [
        'enabled' => false,
    ],
];

// LineWorksClientのインスタンスを作成
$client = new LineWorksClient('default', $botConfig, $globalConfig);

// BotClientを使用する場合
$botClient = new BotClient($client);
$messageResponse = $botClient->message()->sendText('user123', 'こんにちは！');

if ($messageResponse->isSuccess()) {
    echo 'メッセージを送信しました！ ID: ' . $messageResponse->getMessageId();
} else {
    echo 'メッセージの送信に失敗しました。';
}
```

#### 2. Bot機能を使用しない場合（LineWorksClientのみ使用時）

```php
use GuzzleHttp\Client;
use Sumihiro\LineWorksClient\Auth\JwtTokenGenerator;
use Sumihiro\LineWorksClient\Auth\AccessTokenManager;
use Sumihiro\LineWorksClient\LineWorksClient;

// 基本設定（Bot機能を使用しない場合）
$config = [
    // 基本認証に必須のパラメータ
    'service_account' => 'your-service-account',
    'private_key' => 'your-private-key',
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'domain_id' => 'your-domain-id',
    'consumer_key' => 'your-consumer-key',
    'scope' => 'user', // スコープを指定（例: 'user', 'admin', 'contact' など）
    
    // Bot機能を使用しない場合は不要
    // 'bot_id' => 'your-bot-id',
    // 'bot_secret' => 'your-bot-secret',
];

// グローバル設定
$globalConfig = [
    'api_base_url' => 'https://www.worksapis.com/v1.0',
    'cache' => [
        'enabled' => false,
    ],
    'logging' => [
        'enabled' => false,
    ],
];

// LineWorksClientのインスタンスを作成
$client = new LineWorksClient('default', $config, $globalConfig);

// 一般的なAPIリクエストを送信
$response = $client->get('users/info');
$userData = json_decode($response->getBody(), true);

echo 'ユーザー情報: ' . print_r($userData, true);

// 実行時にスコープを変更する場合
$client->setScope('admin');
$adminResponse = $client->get('admin/users');
```

## 複数のボットの設定

`config/lineworks.php`ファイルで複数のボットを設定できます：

```php
'bots' => [
    'default' => [
        // 基本認証に必須のパラメータ
        'service_account' => env('LINEWORKS_SERVICE_ACCOUNT'),
        'private_key' => env('LINEWORKS_PRIVATE_KEY'),
        'client_id' => env('LINEWORKS_CLIENT_ID'),
        'client_secret' => env('LINEWORKS_CLIENT_SECRET'),
        'domain_id' => env('LINEWORKS_DOMAIN_ID'),
        'consumer_key' => env('LINEWORKS_CONSUMER_KEY'),
        'scope' => env('LINEWORKS_SCOPE', 'bot'), // スコープを指定（デフォルトは 'bot'）
        
        // Bot機能を使用する場合に必須のパラメータ
        'bot_id' => env('LINEWORKS_BOT_ID'),
        'bot_secret' => env('LINEWORKS_BOT_SECRET'),
    ],
    'another_bot' => [
        // 基本認証に必須のパラメータ
        'service_account' => env('LINEWORKS_ANOTHER_SERVICE_ACCOUNT'),
        'private_key' => env('LINEWORKS_ANOTHER_PRIVATE_KEY'),
        'client_id' => env('LINEWORKS_ANOTHER_CLIENT_ID'),
        'client_secret' => env('LINEWORKS_ANOTHER_CLIENT_SECRET'),
        'domain_id' => env('LINEWORKS_ANOTHER_DOMAIN_ID'),
        'consumer_key' => env('LINEWORKS_ANOTHER_CONSUMER_KEY'),
        'scope' => env('LINEWORKS_ANOTHER_SCOPE', 'bot'), // スコープを指定（デフォルトは 'bot'）
        
        // Bot機能を使用する場合に必須のパラメータ
        'bot_id' => env('LINEWORKS_ANOTHER_BOT_ID'),
        'bot_secret' => env('LINEWORKS_ANOTHER_BOT_SECRET'),
    ],
],
```

## Bot APIの使用例

### メッセージ送信

```php
use Sumihiro\LineWorksClient\Facades\LineWorks;

// テキストメッセージの送信
$response = LineWorks::botClient()->message()->sendText('user123', 'こんにちは！');

// 一般的なメッセージの送信
$response = LineWorks::botClient()->message()->sendMessage('user123', [
    'content' => [
        'type' => 'text',
        'text' => 'こんにちは！',
    ],
]);

// チャンネルへのメッセージ送信
$response = LineWorks::botClient()->channel()->sendMessage('channel123', [
    'content' => [
        'type' => 'text',
        'text' => 'チャンネルのみなさん、こんにちは！',
    ],
]);
```

### トークルーム（チャンネル）関連

```php
use Sumihiro\LineWorksClient\Facades\LineWorks;

// トークルームの作成
$response = LineWorks::botClient()->channel()->create(
    ['user1@example.com', 'user2@example.com'],
    'プロジェクトA チーム'
);
$channelId = $response->getChannelId();

// トークルーム情報の取得
$channelInfo = LineWorks::botClient()->channel()->info($channelId);
echo 'チャンネル名: ' . $channelInfo->getName();

// トークルームのメンバーリスト取得
$members = LineWorks::botClient()->channel()->members($channelId);
foreach ($members->getMembers() as $member) {
    echo 'メンバー: ' . $member['accountId'];
}

// トークルームからの退室
$result = LineWorks::botClient()->channel()->leave($channelId);
if ($result) {
    echo 'チャンネルから退室しました';
}
```

### リッチメニュー関連

```php
use Sumihiro\LineWorksClient\Facades\LineWorks;

// リッチメニューの作成
$richMenu = [
    'name' => 'マイリッチメニュー',
    'size' => [
        'width' => 2500,
        'height' => 1686,
    ],
    'areas' => [
        [
            'bounds' => [
                'x' => 0,
                'y' => 0,
                'width' => 1250,
                'height' => 1686,
            ],
            'action' => [
                'type' => 'message',
                'text' => 'アクション1',
            ],
        ],
        [
            'bounds' => [
                'x' => 1250,
                'y' => 0,
                'width' => 1250,
                'height' => 1686,
            ],
            'action' => [
                'type' => 'message',
                'text' => 'アクション2',
            ],
        ],
    ],
];

$response = LineWorks::botClient()->richMenu()->create($richMenu);
$richMenuId = $response->getRichMenuId();

// リッチメニューをユーザーに設定
$result = LineWorks::botClient()->richMenu()->setForUser('user123', $richMenuId);

// ユーザーのリッチメニューを取得
$userRichMenu = LineWorks::botClient()->richMenu()->getForUser('user123');

// リッチメニューの削除
$result = LineWorks::botClient()->richMenu()->delete($richMenuId);
```

### ボット管理関連

```php
use Sumihiro\LineWorksClient\Facades\LineWorks;

// ボット情報の取得
$botInfo = LineWorks::botClient()->management()->info();
echo 'ボット名: ' . $botInfo->getName();
echo 'ステータス: ' . $botInfo->getStatus();

// ドメイン情報の取得
$domainInfo = LineWorks::botClient()->management()->domainInfo();
echo 'ドメインID: ' . $domainInfo['domainId'];
echo 'ドメイン名: ' . $domainInfo['domainName'];
```

## スコープについて

LINE WORKS APIでは、アクセストークンを取得する際に「スコープ」を指定する必要があります。スコープによって、アクセスできるAPIが異なります。

主なスコープ：
- `bot`: ボット関連のAPI（デフォルト）
- `user`: ユーザー情報関連のAPI
- `admin`: 管理者向けAPI

複数のスコープを指定する場合は、スペース区切りで指定します：

```php
'scope' => 'bot user calendar', // 複数のスコープを指定
```

## エラーハンドリング

```php
use Sumihiro\LineWorksClient\Exceptions\ApiException;
use Sumihiro\LineWorksClient\Exceptions\AuthenticationException;
use Sumihiro\LineWorksClient\Exceptions\ConfigurationException;

try {
    $response = LineWorks::botClient()->message()->sendText('user123', 'こんにちは！');
} catch (ApiException $e) {
    // API呼び出しエラー
    $statusCode = $e->getStatusCode();
    $responseData = $e->getResponseData();
    
    Log::error('LINE WORKS APIエラー', [
        'status_code' => $statusCode,
        'response' => $responseData,
        'message' => $e->getMessage(),
    ]);
} catch (AuthenticationException $e) {
    // 認証エラー
    Log::error('LINE WORKS認証エラー', [
        'message' => $e->getMessage(),
    ]);
} catch (ConfigurationException $e) {
    // 設定エラー
    Log::error('LINE WORKS設定エラー', [
        'message' => $e->getMessage(),
    ]);
}
```

## ライセンス

MIT
