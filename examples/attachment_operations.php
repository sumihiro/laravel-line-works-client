<?php
/**
 * アタッチメント関連の操作を行うサンプルスクリプト
 * 
 * 使用方法:
 * php examples/attachment_operations.php [操作] [パラメータ...]
 * 
 * 操作:
 * - create: アタッチメントを作成し、アップロードURLを取得します
 *   例: php examples/attachment_operations.php create examples/images/richmenu_sample.jpg
 * 
 * - upload: ファイルをアップロードします
 *   例: php examples/attachment_operations.php upload "アップロードURL" examples/images/richmenu_sample.jpg
 */

// 共通の初期化処理
require_once __DIR__ . '/bootstrap.php';

// アタッチメントクライアントの初期化
$attachmentClient = $client->bot()->attachment();

// アップロードクライアントの初期化
$uploadClient = $client->upload();

// コマンドライン引数の処理
$operation = $argv[1] ?? null;

if (!$operation) {
    showUsage();
    exit(1);
}

try {
    switch ($operation) {
        case 'create':
            createAttachment();
            break;
            
        case 'upload':
            uploadFile();
            break;
            
        default:
            echo "エラー: 不明な操作 '{$operation}' です。\n";
            showUsage();
            exit(1);
    }
} catch (Sumihiro\LineWorksClient\Exceptions\ApiException $e) {
    echo "APIエラー: " . $e->getMessage() . "\n";
    
    if (method_exists($e, 'getResponseData') && $e->getResponseData()) {
        displayResult('エラーレスポンス', $e->getResponseData());
    }
    
    echo "ステータスコード: " . (method_exists($e, 'getStatusCode') ? $e->getStatusCode() : $e->getCode()) . "\n";
    
    if (method_exists($e, 'getRequestUrl') && $e->getRequestUrl()) {
        echo "リクエストURL: " . $e->getRequestUrl() . "\n";
    }
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage() . "\n";
    echo "ステータスコード: " . $e->getCode() . "\n";
}

/**
 * 使用方法を表示
 */
function showUsage()
{
    echo "使用方法: php examples/attachment_operations.php [操作] [パラメータ...]\n\n";
    echo "操作:\n";
    echo "- create: アタッチメントを作成し、アップロードURLを取得します\n";
    echo "  例: php examples/attachment_operations.php create examples/images/richmenu_sample.jpg\n\n";
    echo "- upload: ファイルをアップロードします\n";
    echo "  例: php examples/attachment_operations.php upload \"アップロードURL\" examples/images/richmenu_sample.jpg\n";
}

/**
 * アタッチメントを作成
 */
function createAttachment()
{
    global $argv, $attachmentClient;
    
    $filePath = $argv[2] ?? null;
    
    if (!$filePath) {
        echo "エラー: ファイルパスが指定されていません。\n";
        echo "例: php examples/attachment_operations.php create examples/images/richmenu_sample.jpg\n";
        exit(1);
    }
    
    if (!file_exists($filePath)) {
        echo "エラー: ファイルが存在しません: {$filePath}\n";
        exit(1);
    }
    
    echo "ファイル: {$filePath} のアタッチメントを作成しています...\n";
    
    $response = $attachmentClient->create($filePath);
    
    if ($response->isSuccess()) {
        displayResult('アタッチメント作成結果', [
            'uploadUrl' => $response->getUploadUrl(),
            'fileId' => $response->getFileId(),
        ]);
    } else {
        echo "アタッチメントの作成に失敗しました。\n";
    }
}

/**
 * ファイルをアップロード
 */
function uploadFile()
{
    global $argv, $uploadClient;
    
    $uploadUrl = $argv[2] ?? null;
    $filePath = $argv[3] ?? null;
    
    if (!$uploadUrl || !$filePath) {
        echo "エラー: アップロードURLまたはファイルパスが指定されていません。\n";
        echo "例: php examples/attachment_operations.php upload \"アップロードURL\" examples/images/richmenu_sample.jpg\n";
        exit(1);
    }
    
    if (!file_exists($filePath)) {
        echo "エラー: ファイルが存在しません: {$filePath}\n";
        exit(1);
    }
    
    echo "ファイル: {$filePath} をアップロードしています...\n";
    echo "アップロードURL: {$uploadUrl}\n";
    
    $result = $uploadClient->upload($uploadUrl, $filePath);
    
    displayResult('ファイルアップロード結果', [
        'success' => $result,
    ]);
} 