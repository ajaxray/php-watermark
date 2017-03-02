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
echo $watermark->buildTextMarkCommand($text, __DIR__ . 'img/result_simple.jpg');