<?php

declare(strict_types=1);

namespace Ajaxray\PHPWatermark\CommandBuilders;

interface WatermarkCommandBuilder
{
    /**
     * Build the imagemagick shell command for watermarking with Image
     *
     * @param string $markerImage The image file path to watermark with
     * @param string $output The watermarked output file
     * @param array $options
     * @return string ImageMagick command to watermark with image
     */
    public function getImageMarkCommand(string $markerImage, string $output, array $options): string;

    /**
     * Build the imagemagick shell command for watermarking with Text
     *
     * @param string $text The text content to watermark with
     * @param string $output The watermarked output file
     * @param array $options
     * @return string ImageMagick command to watermark with text
     */
    public function getTextMarkCommand(string $text, string $output, array $options): string;
}
