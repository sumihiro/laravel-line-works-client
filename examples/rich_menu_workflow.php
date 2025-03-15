<?php
/**
 * リッチメニューの一連のワークフローを実行するサンプルスクリプト
 * 
 * このスクリプトは以下の操作を順番に実行します：
 * 1. リッチメニューの作成
 * 2. リッチメニュー画像のアップロード
 * 3. デフォルトリッチメニューの設定
 * 4. 特定ユーザーへのリッチメニュー設定
 * 
 * 使用方法:
 * php examples/rich_menu_workflow.php [ユーザーID]
 * 
 * 例:
 * php examples/rich_menu_workflow.php user@example.com
 * 
 * ユーザーIDを指定しない場合は、設定ファイルのtest_account_idが使用されます。
 */

// 共通の初期化処理
require_once __DIR__ . '/bootstrap.php';

// コマンドライン引数の処理
$userId = $argv[1] ?? null;

// ユーザーIDが指定されていない場合は、設定ファイルのtest_account_idを使用
if (!$userId) {
    $userId = $botConfig['test_account_id'] ?? null;
    
    if (!$userId) {
        echo "エラー: ユーザーIDが指定されておらず、設定ファイルにtest_account_idも設定されていません。\n";
        echo "使用方法: php examples/rich_menu_workflow.php [ユーザーID]\n";
        echo "例: php examples/rich_menu_workflow.php user@example.com\n";
        echo "または、config.phpにtest_account_idを設定してください。\n";
        exit(1);
    }
    
    echo "ユーザーIDが指定されていないため、設定ファイルのtest_account_id: {$userId} を使用します。\n";
}

// サンプル画像のパス
$imagePath = __DIR__ . '/images/richmenu_sample.jpg';

// 画像が存在するか確認
if (!file_exists($imagePath)) {
    echo "エラー: サンプル画像が見つかりません: {$imagePath}\n";
    echo "examples/images ディレクトリに richmenu_sample.jpg を配置してください。\n";
    exit(1);
}

try {
    // ステップ1: リッチメニューの作成
    echo "ステップ1: リッチメニューを作成しています...\n";
    
    // リッチメニューの定義
    $richMenuData = [
        'size' => [
            'width' => 2500,
            'height' => 1686
        ],
        'selected' => false,
        'richmenuName' => 'サンプルリッチメニュー ' . date('Y-m-d H:i:s'),
        'chatBarText' => 'メニューを開く',
        'areas' => [
            [
                'bounds' => [
                    'x' => 0,
                    'y' => 0,
                    'width' => 833,
                    'height' => 843
                ],
                'action' => [
                    'type' => 'message',
                    'label' => 'メニュー1',
                    'text' => 'メニュー1が選択されました'
                ]
            ],
            [
                'bounds' => [
                    'x' => 834,
                    'y' => 0,
                    'width' => 833,
                    'height' => 843
                ],
                'action' => [
                    'type' => 'message',
                    'label' => 'メニュー2',
                    'text' => 'メニュー2が選択されました'
                ]
            ],
            [
                'bounds' => [
                    'x' => 1667,
                    'y' => 0,
                    'width' => 833,
                    'height' => 843
                ],
                'action' => [
                    'type' => 'message',
                    'label' => 'メニュー3',
                    'text' => 'メニュー3が選択されました'
                ]
            ],
            [
                'bounds' => [
                    'x' => 0,
                    'y' => 844,
                    'width' => 833,
                    'height' => 842
                ],
                'action' => [
                    'type' => 'message',
                    'label' => 'メニュー4',
                    'text' => 'メニュー4が選択されました'
                ]
            ],
            [
                'bounds' => [
                    'x' => 834,
                    'y' => 844,
                    'width' => 833,
                    'height' => 842
                ],
                'action' => [
                    'type' => 'message',
                    'label' => 'メニュー5',
                    'text' => 'メニュー5が選択されました'
                ]
            ],
            [
                'bounds' => [
                    'x' => 1667,
                    'y' => 844,
                    'width' => 833,
                    'height' => 842
                ],
                'action' => [
                    'type' => 'message',
                    'label' => 'メニュー6',
                    'text' => 'メニュー6が選択されました'
                ]
            ]
        ]
    ];
    
    // リッチメニューを作成
    $createResponse = $botClient->richMenu()->create($richMenuData);
    $richMenuId = $createResponse->getRichMenuId();
    
    if (!$richMenuId) {
        throw new Exception('リッチメニューの作成に失敗しました');
    }
    
    echo "リッチメニューを作成しました。ID: {$richMenuId}\n";
    
    // ステップ2: リッチメニュー画像のアップロード
    echo "ステップ2: リッチメニュー画像をアップロードしています...\n";
    
    try {
        $uploadResponse = $botClient->richMenu()->uploadImage($richMenuId, $imagePath);
        
        if (!$uploadResponse->isSuccess()) {
            echo "警告: リッチメニュー画像のアップロードに失敗しました。処理を続行します。\n";
        } else {
            echo "リッチメニュー画像をアップロードしました\n";
        }
    } catch (Exception $e) {
        var_dump($e);
        echo "警告: リッチメニュー画像のアップロードに失敗しました: " . $e->getMessage() . "\n";
        echo "処理を続行します。\n";
    }
    
    // ステップ3: デフォルトリッチメニューの設定
    echo "ステップ3: デフォルトリッチメニューを設定しています...\n";
    
    try {
        $defaultResponse = $botClient->richMenu()->setDefault($richMenuId);
        
        if (!$defaultResponse->isSuccess()) {
            echo "警告: デフォルトリッチメニューの設定に失敗しました。処理を続行します。\n";
        } else {
            echo "デフォルトリッチメニューを設定しました\n";
        }
    } catch (Exception $e) {
        echo "警告: デフォルトリッチメニューの設定に失敗しました: " . $e->getMessage() . "\n";
        echo "処理を続行します。\n";
    }
    
    // ステップ4: 特定ユーザーへのリッチメニュー設定
    echo "ステップ4: ユーザー {$userId} にリッチメニューを設定しています...\n";
    
    try {
        $userResponse = $botClient->richMenu()->setForUser($userId, $richMenuId);
        
        if (!$userResponse->isSuccess()) {
            echo "警告: ユーザー {$userId} へのリッチメニュー設定に失敗しました。\n";
        } else {
            echo "ユーザー {$userId} にリッチメニューを設定しました\n";
        }
    } catch (Exception $e) {
        echo "警告: ユーザー {$userId} へのリッチメニュー設定に失敗しました: " . $e->getMessage() . "\n";
    }
    
    // 完了メッセージ
    echo "\n全てのステップが完了しました！\n";
    echo "作成したリッチメニューID: {$richMenuId}\n";
    echo "\n";
    echo "以下のコマンドで確認できます:\n";
    echo "- リッチメニューの詳細確認: php examples/rich_menu_operations.php get {$richMenuId}\n";
    echo "- デフォルトリッチメニューの確認: php examples/rich_menu_operations.php get-default\n";
    echo "- ユーザーのリッチメニューの確認: php examples/rich_menu_operations.php get-user {$userId}\n";
    
    echo "\n";
    echo "注意: 一部の操作が失敗した場合は、LINE WORKSのAPIドキュメントを確認して、\n";
    echo "正しいエンドポイントを使用しているか確認してください。\n";
    
} catch (Exception $e) {
    echo "エラーが発生しました: " . $e->getMessage() . "\n";
    
    // 詳細なエラー情報を表示
    if ($e instanceof Sumihiro\LineWorksClient\Exceptions\ApiException) {
        echo "エラーの詳細:\n";
        echo "ステータスコード: " . $e->getStatusCode() . "\n";
        
        $responseData = $e->getResponseData();
        if ($responseData) {
            echo "レスポンスデータ: " . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
        
        if ($e->getPrevious()) {
            echo "元の例外: " . $e->getPrevious()->getMessage() . "\n";
        }
    }
    
    exit(1);
} 