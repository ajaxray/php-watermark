<?php
use Ajaxray\PHPWatermark\Watermark;

include __DIR__.'/../vendor/autoload.php';

$watermark = new Watermark(__DIR__ . '/img/mountain.jpg');
$watermark->setFont('Arial');
$watermark->setFontSize(48);
$watermark->setOpacity(.2);
$watermark->setRotate(-15);
$watermark->setOffset(10, 100);
$watermark->setPosition(Watermark::POSITION_BOTTOM_RIGHT);

$text = "ajaxray.com";
$watermark->withText($text, __DIR__ . '/img/result_simple.jpg');
// echo $watermark->buildTextMarkCommand($text, __DIR__ . 'img/result_simple.jpg');

$watermark->setTiled()->setTileSize(200, 150);
$watermark->setFontSize(24);
$watermark->withText($text, __DIR__ . '/img/result_tiled.jpg');
// echo $watermark->buildTextMarkCommand($text, __DIR__ . 'img/result_tiled.jpg'). PHP_EOL;

// Watermarking with image
$imgMark = new Watermark(__DIR__ . '/img/mountain.jpg');
$imgMark->setPosition(Watermark::POSITION_TOP_RIGHT);
$imgMark->setOffset(50, 50);
$imgMark->setOpacity(.3);
$imgMark->withImage(__DIR__ . '/img/php.png', __DIR__ . '/img/result_logo.jpg');
