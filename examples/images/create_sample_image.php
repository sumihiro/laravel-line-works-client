<?php
/**
 * リッチメニュー用のサンプル画像を生成するスクリプト
 * 
 * 使用方法:
 * php examples/images/create_sample_image.php
 */

// 画像サイズ（LINE WORKSリッチメニューの推奨サイズ）
$width = 2500;
$height = 1686;

// 画像を作成
$image = imagecreatetruecolor($width, $height);

// 色を定義
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$blue = imagecolorallocate($image, 0, 0, 255);
$red = imagecolorallocate($image, 255, 0, 0);

// 背景を白で塗りつぶす
imagefill($image, 0, 0, $white);

// 左右のエリアを区切る線を描画
imageline($image, $width / 2, 0, $width / 2, $height, $black);

// 左側のエリアにテキストを描画
$text1 = "アクション1";
$font = 5; // 組み込みフォント
$text1_width = imagefontwidth($font) * strlen($text1);
$text1_height = imagefontheight($font);
$text1_x = ($width / 4) - ($text1_width / 2);
$text1_y = ($height / 2) - ($text1_height / 2);
imagestring($image, $font, $text1_x, $text1_y, $text1, $blue);

// 右側のエリアにテキストを描画
$text2 = "アクション2";
$text2_width = imagefontwidth($font) * strlen($text2);
$text2_height = imagefontheight($font);
$text2_x = ($width * 3 / 4) - ($text2_width / 2);
$text2_y = ($height / 2) - ($text2_height / 2);
imagestring($image, $font, $text2_x, $text2_y, $text2, $red);

// タイトルを描画
$title = "LINE WORKS リッチメニューサンプル";
$title_width = imagefontwidth($font) * strlen($title);
$title_x = ($width / 2) - ($title_width / 2);
$title_y = 50;
imagestring($image, $font, $title_x, $title_y, $title, $black);

// 画像をファイルに保存
$filename = __DIR__ . '/richmenu_sample.jpg';
imagejpeg($image, $filename);
imagedestroy($image);

echo "サンプル画像を作成しました: {$filename}\n"; 