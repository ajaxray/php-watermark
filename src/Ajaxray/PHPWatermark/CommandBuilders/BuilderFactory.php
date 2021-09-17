<?php
declare(strict_types=1);

namespace Ajaxray\PHPWatermark\CommandBuilders;

class BuilderFactory
{
    const PATTERN_MIME_IMAGE = '/^image\/\w{1,4}$/';
    const PATTERN_MIME_PDF = '/^application\/(x\-)?pdf$/';

    public static function getCommandBuilder(string $sourcePath) : WatermarkCommandBuilder
    {
        $mimeType = mime_content_type($sourcePath);

        if (preg_match(self::PATTERN_MIME_IMAGE, $mimeType)) {
            return new ImageCommandBuilder($sourcePath);
        } elseif (preg_match(self::PATTERN_MIME_PDF, $mimeType)) {
            return new PDFCommandBuilder($sourcePath);
        } else {
            throw new \InvalidArgumentException("The source file type $mimeType is not supported.");
        }
    }
}