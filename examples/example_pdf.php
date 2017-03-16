<?php
use Ajaxray\PHPWatermark\Watermark;

include __DIR__.'/../vendor/autoload.php';

// Watermark with text
$watermark = new Watermark(__DIR__ . '/pdf/The_Man_In_The_Red_Underpants.pdf');
$watermark->setFont('Arial');
$watermark->setFontSize(18);
$watermark->setRotate(345);
$watermark->setOffset(20, 60);
$watermark->setPosition(Watermark::POSITION_BOTTOM_RIGHT);
//$watermark->setDebug(true);

$text = "ajaxray.com";
$watermark->withText($text, __DIR__ . '/pdf/result_text.pdf');

// Watermarking with image
$watermark->setPosition(Watermark::POSITION_TOP_RIGHT);
$watermark->setOffset(50, 50);
$watermark->setOpacity(.2);
$watermark->setTiled();
$watermark->withImage(__DIR__ . '/img/php.png', __DIR__ . '/pdf/result_img.pdf');
