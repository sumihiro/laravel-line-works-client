<?php
/**
 * リッチメニュー関連の操作を行うサンプルスクリプト
 * 
 * 使用方法:
 * php examples/rich_menu_operations.php [操作] [パラメータ...]
 * 
 * 操作:
 * - list: リッチメニューの一覧を取得します
 *   例: php examples/rich_menu_operations.php list
 * 
 * - get: リッチメニューの詳細情報を取得します
 *   例: php examples/rich_menu_operations.php get rich-menu-id
 * 
 * - create: リッチメニューを作成します
 *   例: php examples/rich_menu_operations.php create "テストメニュー"
 * 
 * - upload: リッチメニュー画像をアップロードします
 *   例: php examples/rich_menu_operations.php upload rich-menu-id /path/to/image.jpg
 * 
 * - set-user: ユーザーにリッチメニューを設定します
 *   例: php examples/rich_menu_operations.php set-user rich-menu-id user@example.com
 * 
 * - get-user: ユーザーのリッチメニューを取得します
 *   例: php examples/rich_menu_operations.php get-user user@example.com
 * 
 * - delete-user: ユーザーのリッチメニューを削除します
 *   例: php examples/rich_menu_operations.php delete-user user@example.com
 * 
 * - set-default: デフォルトリッチメニューを設定します
 *   例: php examples/rich_menu_operations.php set-default rich-menu-id
 * 
 * - get-default: デフォルトリッチメニューを取得します
 *   例: php examples/rich_menu_operations.php get-default
 * 
 * - delete-default: デフォルトリッチメニューを削除します
 *   例: php examples/rich_menu_operations.php delete-default
 * 
 * - delete: リッチメニューを削除します
 *   例: php examples/rich_menu_operations.php delete rich-menu-id
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
        case 'list':
            listRichMenus();
            break;
            
        case 'get':
            getRichMenu();
            break;
            
        case 'create':
            createRichMenu();
            break;
            
        case 'upload':
            uploadRichMenuImage();
            break;
            
        case 'set-user':
            setRichMenuForUser();
            break;
            
        case 'get-user':
            getRichMenuForUser();
            break;
            
        case 'delete-user':
            deleteRichMenuForUser();
            break;
            
        case 'set-default':
            setDefaultRichMenu();
            break;
            
        case 'get-default':
            getDefaultRichMenu();
            break;
            
        case 'delete-default':
            deleteDefaultRichMenu();
            break;
            
        case 'delete':
            deleteRichMenu();
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
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage() . "\n";
}

/**
 * 使用方法を表示
 */
function showUsage()
{
    echo "使用方法: php examples/rich_menu_operations.php [操作] [パラメータ...]\n\n";
    echo "操作:\n";
    echo "- list: リッチメニューの一覧を取得します\n";
    echo "  例: php examples/rich_menu_operations.php list\n\n";
    echo "- get: リッチメニューの詳細情報を取得します\n";
    echo "  例: php examples/rich_menu_operations.php get rich-menu-id\n\n";
    echo "- create: リッチメニューを作成します\n";
    echo "  例: php examples/rich_menu_operations.php create \"テストメニュー\"\n\n";
    echo "- upload: リッチメニュー画像をアップロードします\n";
    echo "  例: php examples/rich_menu_operations.php upload rich-menu-id /path/to/image.jpg\n\n";
    echo "- set-user: ユーザーにリッチメニューを設定します\n";
    echo "  例: php examples/rich_menu_operations.php set-user rich-menu-id user@example.com\n\n";
    echo "- get-user: ユーザーのリッチメニューを取得します\n";
    echo "  例: php examples/rich_menu_operations.php get-user user@example.com\n\n";
    echo "- delete-user: ユーザーのリッチメニューを削除します\n";
    echo "  例: php examples/rich_menu_operations.php delete-user user@example.com\n\n";
    echo "- set-default: デフォルトリッチメニューを設定します\n";
    echo "  例: php examples/rich_menu_operations.php set-default rich-menu-id\n\n";
    echo "- get-default: デフォルトリッチメニューを取得します\n";
    echo "  例: php examples/rich_menu_operations.php get-default\n\n";
    echo "- delete-default: デフォルトリッチメニューを削除します\n";
    echo "  例: php examples/rich_menu_operations.php delete-default\n\n";
    echo "- delete: リッチメニューを削除します\n";
    echo "  例: php examples/rich_menu_operations.php delete rich-menu-id\n";
}

/**
 * リッチメニューの一覧を取得
 */
function listRichMenus()
{
    global $botClient;
    
    echo "リッチメニューの一覧を取得します...\n";
    
    $response = $botClient->richMenu()->list();
    
    if ($response->hasRichMenus()) {
        displayResult('リッチメニュー一覧', [
            'richmenus' => $response->getRichMenus(),
            'responseMetaData' => ['nextCursor' => $response->getNextCursor()],
        ]);
    } else {
        echo "リッチメニューが存在しません。\n";
    }
}

/**
 * リッチメニューの詳細情報を取得
 */
function getRichMenu()
{
    global $argv, $botClient;
    
    $richMenuId = $argv[2] ?? null;
    
    if (!$richMenuId) {
        echo "エラー: リッチメニューIDが指定されていません。\n";
        echo "例: php examples/rich_menu_operations.php get rich-menu-id\n";
        exit(1);
    }
    
    echo "リッチメニューID: {$richMenuId} の詳細情報を取得します...\n";
    
    $response = $botClient->richMenu()->get($richMenuId);
    
    $result = [
        'richmenuId' => $response->getRichMenuId(),
        'size' => $response->getSize(),
        'richmenuName' => $response->getName(),
        'areas' => $response->getAreas(),
    ];
    
    if ($response->getChatBarText()) {
        $result['chatBarText'] = $response->getChatBarText();
    }
    
    if ($response->isSelected() !== null) {
        $result['selected'] = $response->isSelected();
    }
    
    displayResult('リッチメニュー詳細', $result);
}

/**
 * リッチメニューを作成
 */
function createRichMenu()
{
    global $argv, $botClient;
    
    $name = $argv[2] ?? 'テストリッチメニュー';
    
    echo "リッチメニュー '{$name}' を作成します...\n";
    
    // リッチメニューのデータを作成
    $richMenuData = [
        'size' => [
            'width' => 2500,
            'height' => 1686,
        ],
        'selected' => false,
        'richmenuName' => $name,
        'chatBarText' => 'メニュー',
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
    
    $response = $botClient->richMenu()->create($richMenuData);
    
    if ($response->isSuccess()) {
        displayResult('リッチメニュー作成結果', [
            'richMenuId' => $response->getRichMenuId(),
            'name' => $name,
        ]);
    } else {
        echo "リッチメニューの作成に失敗しました。\n";
    }
}

/**
 * リッチメニュー画像をアップロード
 */
function uploadRichMenuImage()
{
    global $argv, $botClient;
    
    $richMenuId = $argv[2] ?? null;
    $imagePath = $argv[3] ?? null;
    
    if (!$richMenuId || !$imagePath) {
        echo "エラー: リッチメニューIDまたは画像パスが指定されていません。\n";
        echo "例: php examples/rich_menu_operations.php upload rich-menu-id /path/to/image.jpg\n";
        exit(1);
    }
    
    if (!file_exists($imagePath)) {
        echo "エラー: 指定された画像ファイルが存在しません: {$imagePath}\n";
        exit(1);
    }
    
    echo "リッチメニューID: {$richMenuId} に画像をアップロードします...\n";
    echo "画像パス: {$imagePath}\n";
    
    $response = $botClient->richMenu()->uploadImage($richMenuId, $imagePath);
    
    if ($response->isSuccess()) {
        displayResult('リッチメニュー画像アップロード結果', [
            'success' => true,
        ]);
    } else {
        echo "リッチメニュー画像のアップロードに失敗しました。\n";
    }
}

/**
 * ユーザーにリッチメニューを設定
 */
function setRichMenuForUser()
{
    global $argv, $botClient, $botConfig;
    
    $richMenuId = $argv[2] ?? null;
    $accountId = $argv[3] ?? $botConfig['test_account_id'] ?? null;
    
    if (!$richMenuId) {
        echo "エラー: リッチメニューIDが指定されていません。\n";
        echo "例: php examples/rich_menu_operations.php set-user rich-menu-id user@example.com\n";
        exit(1);
    }
    
    if (!$accountId) {
        echo "エラー: アカウントIDが指定されていません。\n";
        echo "例: php examples/rich_menu_operations.php set-user rich-menu-id user@example.com\n";
        exit(1);
    }
    
    echo "アカウントID: {$accountId} にリッチメニューID: {$richMenuId} を設定します...\n";
    
    $response = $botClient->richMenu()->setForUser($accountId, $richMenuId);
    
    if ($response->isSuccess()) {
        displayResult('ユーザーへのリッチメニュー設定結果', [
            'success' => true,
        ]);
    } else {
        echo "ユーザーへのリッチメニュー設定に失敗しました。\n";
    }
}

/**
 * ユーザーのリッチメニューを取得
 */
function getRichMenuForUser()
{
    global $argv, $botClient, $botConfig;
    
    $accountId = $argv[2] ?? $botConfig['test_account_id'] ?? null;
    
    if (!$accountId) {
        echo "エラー: アカウントIDが指定されていません。\n";
        echo "例: php examples/rich_menu_operations.php get-user user@example.com\n";
        exit(1);
    }
    
    echo "アカウントID: {$accountId} のリッチメニューを取得します...\n";
    
    $response = $botClient->richMenu()->getForUser($accountId);
    
    if ($response->getRichMenuId()) {
        displayResult('ユーザーのリッチメニュー', [
            'richMenuId' => $response->getRichMenuId(),
        ]);
    } else {
        echo "ユーザーにリッチメニューが設定されていません。\n";
    }
}

/**
 * ユーザーのリッチメニューを削除
 */
function deleteRichMenuForUser()
{
    global $argv, $botClient, $botConfig;
    
    $accountId = $argv[2] ?? $botConfig['test_account_id'] ?? null;
    
    if (!$accountId) {
        echo "エラー: アカウントIDが指定されていません。\n";
        echo "例: php examples/rich_menu_operations.php delete-user user@example.com\n";
        exit(1);
    }
    
    echo "アカウントID: {$accountId} のリッチメニューを削除します...\n";
    
    $response = $botClient->richMenu()->deleteForUser($accountId);
    
    if ($response->isSuccess()) {
        displayResult('ユーザーのリッチメニュー削除結果', [
            'success' => true,
        ]);
    } else {
        echo "ユーザーのリッチメニュー削除に失敗しました。\n";
    }
}

/**
 * デフォルトリッチメニューを設定
 */
function setDefaultRichMenu()
{
    global $argv, $botClient;
    
    $richMenuId = $argv[2] ?? null;
    
    if (!$richMenuId) {
        echo "エラー: リッチメニューIDが指定されていません。\n";
        echo "例: php examples/rich_menu_operations.php set-default rich-menu-id\n";
        exit(1);
    }
    
    echo "リッチメニューID: {$richMenuId} をデフォルトリッチメニューとして設定します...\n";
    
    $response = $botClient->richMenu()->setDefault($richMenuId);
    
    if ($response->isSuccess()) {
        displayResult('デフォルトリッチメニュー設定結果', [
            'success' => true,
        ]);
    } else {
        echo "デフォルトリッチメニューの設定に失敗しました。\n";
    }
}

/**
 * デフォルトリッチメニューを取得
 */
function getDefaultRichMenu()
{
    global $botClient;
    
    echo "デフォルトリッチメニューを取得します...\n";
    
    $response = $botClient->richMenu()->getDefault();
    
    if ($response->getRichMenuId()) {
        displayResult('デフォルトリッチメニュー', [
            'richMenuId' => $response->getRichMenuId(),
        ]);
    } else {
        echo "デフォルトリッチメニューが設定されていません。\n";
    }
}

/**
 * デフォルトリッチメニューを削除
 */
function deleteDefaultRichMenu()
{
    global $botClient;
    
    echo "デフォルトリッチメニューを削除します...\n";
    
    $response = $botClient->richMenu()->deleteDefault();
    
    if ($response->isSuccess()) {
        displayResult('デフォルトリッチメニュー削除結果', [
            'success' => true,
        ]);
    } else {
        echo "デフォルトリッチメニューの削除に失敗しました。\n";
    }
}

/**
 * リッチメニューを削除
 */
function deleteRichMenu()
{
    global $argv, $botClient;
    
    $richMenuId = $argv[2] ?? null;
    
    if (!$richMenuId) {
        echo "エラー: リッチメニューIDが指定されていません。\n";
        echo "例: php examples/rich_menu_operations.php delete rich-menu-id\n";
        exit(1);
    }
    
    echo "リッチメニューID: {$richMenuId} を削除します...\n";
    
    $response = $botClient->richMenu()->delete($richMenuId);
    
    if ($response->isSuccess()) {
        displayResult('リッチメニュー削除結果', [
            'success' => true,
        ]);
    } else {
        echo "リッチメニューの削除に失敗しました。\n";
    }
} 