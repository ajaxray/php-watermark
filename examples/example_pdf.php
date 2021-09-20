<?php
use Ajaxray\PHPWatermark\Watermark;

include 'vendor/autoload.php';

$watermark = new Watermark(__DIR__ . '/pdf/The_Man_In_The_Red_Underpants.pdf');

// Watermark with text
$text = "ajaxray.com";

$watermark->withText($text)
    ->setFont('Arial')
    ->setFontSize(18)
    ->setRotate(345)
    ->setOffset(20, 60)
    ->setPosition(Watermark::POSITION_BOTTOM_RIGHT)
    ->write(__DIR__ . '/pdf/result_text.pdf');

// Watermarking with image
$watermark->withImage(__DIR__ . '/img/php.png')
    ->setPosition(Watermark::POSITION_TOP_RIGHT)
    ->setOffset(50, 50)
    ->setOpacity(.5)
    ->write( __DIR__ . '/pdf/result_img.pdf');
