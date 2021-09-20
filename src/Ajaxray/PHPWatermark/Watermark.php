<?php declare(strict_types=1);

namespace Ajaxray\PHPWatermark;

use Ajaxray\PHPWatermark\CommandBuilders\CommandBuilderFactory;
use Ajaxray\PHPWatermark\CommandBuilders\WatermarkCommandBuilder;

class Watermark
{
    // Anchors to place text/image
    public const POSITION_TOP_LEFT = 'NorthWest';
    public const POSITION_TOP = 'North';
    public const POSITION_TOP_RIGHT = 'NorthEast';
    public const POSITION_LEFT = 'West';
    public const POSITION_CENTER = 'Center';
    public const POSITION_RIGHT = 'East';
    public const POSITION_BOTTOM_LEFT = 'SouthWest';
    public const POSITION_BOTTOM = 'South';
    public const POSITION_BOTTOM_RIGHT = 'SouthEast';

    public const STYLE_IMG_DISSOLVE = 1;
    public const STYLE_IMG_COLORLESS = 2;
    public const STYLE_TEXT_BEVEL = 1;
    public const STYLE_TEXT_DARK = 2;
    public const STYLE_TEXT_LIGHT = 3;

    // Type of Watermarking
    public const MARKER_TEXT = 1;
    public const MARKER_IMG = 2;

    private string $source;
    private string $marker;
    private int $markerType;
    private WatermarkCommandBuilder $commandBuilder;

    private array $options = [
        'position' => 'Center',
        'offsetX' => 0,
        'offsetY' => 0,
        'tiled' => false,
        'tileSize' => [100, 100],
        'font' => 'Arial',
        'fontSize' => 24,
        'opacity' => 0.3,
        'rotate' => 0,
        'style' => 1, // STYLE_IMG_DISSOLVE or STYLE_TEXT_BEVEL
    ];

    public function __construct(string $source)
    {
        $this->ensureExists($source);

        $this->source = $source;
        $this->commandBuilder = CommandBuilderFactory::getCommandBuilder($source);
    }

    public function withText(string $text): self
    {
        $this->marker = $text;
        $this->markerType = self::MARKER_TEXT;

        return $this;
    }

    public function withImage(string $imagePath): self
    {
        $this->ensureExists($imagePath);

        $this->marker = $imagePath;
        $this->markerType = self::MARKER_IMG;

        return $this;
    }

    /**
     * Make the executable ImageMagick command
     */
    public function getCommand(?string $outputPath = null): string
    {
        $destination = $outputPath ?? $this->source;

        $this->ensureWritable($outputPath ? dirname($destination) : $destination);

        if ($this->markerType === self::MARKER_IMG) {
            return $this->commandBuilder->getImageMarkCommand($this->marker, $destination, $this->options);
        }

        if ($this->markerType === self::MARKER_TEXT) {
            return $this->commandBuilder->getTextMarkCommand($this->marker, $destination, $this->options);
        }

        throw new \LogicException("Unknown marker type set: {$this->markerType}.");
    }

    /**
     * Write the output image
     */
    public function write(?string $outputPath = null): bool
    {
        $output = $returnCode = null;
        exec($this->getCommand($outputPath), $output, $returnCode);

        return empty($output) && $returnCode === 0;
    }

    public function setPosition(string $position): self
    {
        if (! in_array($position, $this->supportedPositionList())) {
            throw new \InvalidArgumentException("Position $position is not supported! Use Watermark::POSITION_* constants.");
        }

        $this->options['position'] = $position;

        return $this;
    }

    public function setOffset(int $offsetX, int $offsetY): self
    {
        $this->options['offsetX'] = $offsetX;
        $this->options['offsetY'] = $offsetY;

        return $this;
    }

    public function setStyle(int $style): self
    {
        $this->options['style'] = $style;

        return $this;
    }

    public function setTiled(bool $tiled = true): self
    {
        $this->options['tiled'] = $tiled;

        return $this;
    }

    public function setTileSize(int $width, int $height): self
    {
        $this->options['tileSize'] = [$width, $height];

        return $this;
    }

    /**
     * Font name should be one of the list displayed by `convert -list font` command
     */
    public function setFont(string $font): self
    {
        $this->options['font'] = $font;

        return $this;
    }

    public function setFontSize(int $fontSize): self
    {
        $this->options['fontSize'] = $fontSize;

        return $this;
    }

    /**
     * @param float $opacity Between .1 (very transparent) to .9 (almost opaque).
     */
    public function setOpacity(float $opacity): self
    {
        if ($opacity < 0 || $opacity > 1) {
            throw new \InvalidArgumentException('Opacity should be float between 0 to 1!');
        }

        $this->options['opacity'] = $opacity;

        return $this;
    }

    /**
     * @param int $rotate Degree of rotation
     */
    public function setRotate(int $rotate): self
    {
        $this->options['rotate'] = abs($rotate);

        return $this;
    }

    final public function supportedPositionList(): array
    {
        return [
            self::POSITION_TOP_LEFT,
            self::POSITION_TOP,
            self::POSITION_TOP_RIGHT,
            self::POSITION_RIGHT,
            self::POSITION_CENTER,
            self::POSITION_LEFT,
            self::POSITION_BOTTOM_LEFT,
            self::POSITION_BOTTOM,
            self::POSITION_BOTTOM_RIGHT,
        ];
    }

    private function ensureExists(string $filePath): void
    {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("The specified file $filePath was not found!");
        }
    }

    private function ensureWritable(string $dirPath): void
    {
        if (! is_writable($dirPath)) {
            throw new \InvalidArgumentException("The specified destination $dirPath is not writable!");
        }
    }
}
