<?php
/**
 * 秘密鍵の形式を確認するスクリプト
 * 
 * このスクリプトは、秘密鍵の形式を確認し、問題があれば修正します。
 */

// オートローダーの読み込み
require_once __DIR__ . '/../vendor/autoload.php';

// 設定ファイルの読み込み
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    echo "エラー: config.php ファイルが見つかりません。\n";
    echo "examples/config.example.php をコピーして examples/config.php を作成し、実際の認証情報を設定してください。\n";
    exit(1);
}

// 設定の読み込み
$config = require $configFile;

// 秘密鍵を取得
$privateKey = $config['private_key'];

echo "=== 秘密鍵の確認 ===\n";
echo "秘密鍵の長さ: " . strlen($privateKey) . " バイト\n";
echo "秘密鍵の先頭部分: " . substr($privateKey, 0, 40) . "...\n";
echo "秘密鍵の末尾部分: " . substr($privateKey, -40) . "\n";
echo "\n";

// 秘密鍵の形式を確認
$hasBeginMarker = strpos($privateKey, '-----BEGIN PRIVATE KEY-----') !== false;
$hasEndMarker = strpos($privateKey, '-----END PRIVATE KEY-----') !== false;

echo "BEGIN PRIVATE KEY マーカーの存在: " . ($hasBeginMarker ? 'あり' : 'なし') . "\n";
echo "END PRIVATE KEY マーカーの存在: " . ($hasEndMarker ? 'あり' : 'なし') . "\n";
echo "\n";

// 秘密鍵の内容を行ごとに表示
echo "=== 秘密鍵の内容（行ごと） ===\n";
$lines = explode("\n", $privateKey);
foreach ($lines as $index => $line) {
    echo "行 " . ($index + 1) . " (" . strlen($line) . " バイト): " . substr($line, 0, 40) . (strlen($line) > 40 ? "..." : "") . "\n";
}
echo "\n";

// 秘密鍵の形式を修正
echo "=== 秘密鍵の形式を修正 ===\n";

// 余分な空白や改行を削除
$cleanedKey = trim($privateKey);

// マーカーがない場合は追加
if (!$hasBeginMarker || !$hasEndMarker) {
    // マーカーを削除（既存のマーカーが不完全な場合）
    $cleanedKey = preg_replace('/-----BEGIN PRIVATE KEY-----.*?-----END PRIVATE KEY-----/s', '', $cleanedKey);
    $cleanedKey = preg_replace('/-----BEGIN PRIVATE KEY-----/s', '', $cleanedKey);
    $cleanedKey = preg_replace('/-----END PRIVATE KEY-----/s', '', $cleanedKey);
    
    // 余分な空白や改行を削除
    $cleanedKey = preg_replace('/\s+/', '', $cleanedKey);
    
    // PEM形式に変換
    $cleanedKey = "-----BEGIN PRIVATE KEY-----\n" . 
                  chunk_split($cleanedKey, 64, "\n") . 
                  "-----END PRIVATE KEY-----";
}

echo "修正後の秘密鍵の長さ: " . strlen($cleanedKey) . " バイト\n";
echo "修正後の秘密鍵の先頭部分: " . substr($cleanedKey, 0, 40) . "...\n";
echo "修正後の秘密鍵の末尾部分: " . substr($cleanedKey, -40) . "\n";
echo "\n";

// 修正後の秘密鍵の内容を行ごとに表示
echo "=== 修正後の秘密鍵の内容（行ごと） ===\n";
$lines = explode("\n", $cleanedKey);
foreach ($lines as $index => $line) {
    echo "行 " . ($index + 1) . " (" . strlen($line) . " バイト): " . substr($line, 0, 40) . (strlen($line) > 40 ? "..." : "") . "\n";
}
echo "\n";

// 修正後の秘密鍵を保存するか確認
echo "修正後の秘密鍵を private.key.fixed に保存しますか？ (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) === 'y') {
    file_put_contents(__DIR__ . '/private.key.fixed', $cleanedKey);
    echo "修正後の秘密鍵を " . __DIR__ . "/private.key.fixed に保存しました。\n";
    echo "config.php を修正して、この秘密鍵を使用するようにしてください。\n";
} else {
    echo "秘密鍵の保存をキャンセルしました。\n";
}
fclose($handle); 