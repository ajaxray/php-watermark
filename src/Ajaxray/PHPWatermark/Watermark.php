<?php
declare(strict_types=1);

namespace Ajaxray\PHPWatermark;

use Ajaxray\PHPWatermark\CommandBuilders\BuilderFactory;
use Ajaxray\PHPWatermark\CommandBuilders\WatermarkCommandBuilder;
use InvalidArgumentException;
use LogicException;

/**
 * Class Watermark
 *
 * @package Ajaxray\PHPWatermark
 */
class Watermark
{
    // Anchors to place text/image
    const POSITION_TOP_LEFT = 'NorthWest';
    const POSITION_TOP = 'North';
    const POSITION_TOP_RIGHT = 'NorthEast';
    const POSITION_LEFT = 'West';
    const POSITION_CENTER = 'Center';
    const POSITION_RIGHT = 'East';
    const POSITION_BOTTOM_LEFT = 'SouthWest';
    const POSITION_BOTTOM = 'South';
    const POSITION_BOTTOM_RIGHT = 'SouthEast';

    const STYLE_IMG_DISSOLVE = 1;
    const STYLE_IMG_COLORLESS = 2;

    const STYLE_TEXT_BEVEL = 1;
    const STYLE_TEXT_DARK = 2;
    const STYLE_TEXT_LIGHT = 3;

    // Type of Watermarking
    const MARKER_TEXT = 1;
    const MARKER_IMG = 2;


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

    /** @var string The Text/filePath to watermark with */
    private string $marker;

    /** @var int $markerType Type of the marker. MARKER_* values applicable */
    private int $markerType;

    private WatermarkCommandBuilder $commandBuilder;

    /**
     * Watermark constructor.
     *
     * @param string $source Source Image
     * @return self
     */
    public function __construct(private string $source)
    {
        $this->ensureExists($this->source);

        $this->commandBuilder = BuilderFactory::getCommandBuilder($this->source);

        return $this;
    }

    /**
     * @param string $text Text to be used as watermark
     * @return Watermark
     */
    public function withText(string $text): static
    {
        $this->marker = $text;
        $this->markerType = self::MARKER_TEXT;

        return $this;
    }

    /**
     * @param string $marker Image path to be used as watermark
     * @return self
     */
    public function withImage(string $marker): static
    {
        $this->ensureExists($marker);

        $this->marker = $marker;
        $this->markerType = self::MARKER_IMG;

        return $this;
    }

    /**
     * Make the executable ImageMagick command
     *
     * @throws LogicException
     * @param string|null $outputPath
     * @return string
     */
    public function getCommand(?string $outputPath = null): string
    {
        $destination = $outputPath ?? $this->source;

        $this->ensureWritable(($outputPath ? dirname($destination) : $destination));

        if ($this->markerType == self::MARKER_IMG) {
            return $this->commandBuilder->getImageMarkCommand($this->marker, $destination, $this->options);
        } elseif ($this->markerType == self::MARKER_TEXT) {
            return $this->commandBuilder->getTextMarkCommand($this->marker, $destination, $this->options);
        } else {
            throw new LogicException("Text or Image was not set to watermark with");
        }
    }

    /**
     * Write the output image
     *
     * @param string|null $outputPath Path of output image. Overwrite source image in case of null
     * @return bool
     */
    public function write(?string $outputPath = null): bool
    {
        $output = $returnCode = null;
        exec($this->getCommand($outputPath), $output, $returnCode);

        return (empty($output) && $returnCode === 0);
    }


    /**
     * @param string $position  One of Watermark::POSITION_* constants
     * @return self
     */
    public function setPosition(string $position): static
    {
        if (in_array($position, $this->supportedPositionList())) {
            $this->options['position'] = $position;
        } else {
            throw new InvalidArgumentException("Position $position is not supported! Use Watermark::POSITION_* constants.");
        }

        return $this;
    }

    /**
     * @param int $offsetX
     * @param int $offsetY
     *
     * @return self
     */
    public function setOffset(int $offsetX, int $offsetY): static
    {
        $this->options['offsetX'] = intval($offsetX);
        $this->options['offsetY'] = intval($offsetY);

        return $this;
    }

    /**
     * @param int $style
     * @return Watermark
     */
    public function setStyle($style): static
    {
        $this->options['style'] = $style;

        return $this;
    }

    /**
     * @param bool $tiled
     * @return Watermark
     */
    public function setTiled(bool $tiled = true): static
    {
        $this->options['tiled'] = $tiled;

        return $this;
    }

    /**
     * Size of tile box.
     *
     * @param int $width
     * @param int $height
     * @return Watermark
     */
    public function setTileSize(int $width, int $height): static
    {
        $this->options['tileSize'] = [$width, $height];

        return $this;
    }

    /**
     * Font name. Should be one of the list displayed by `convert -list font` command
     *
     * @param string $font
     * @return Watermark
     */
    public function setFont(string $font): static
    {
        $this->options['font'] = $font;

        return $this;
    }

    /**
     * @param int $fontSize
     * @return Watermark
     */
    public function setFontSize(int $fontSize): static
    {
        $this->options['fontSize'] = $fontSize;

        return $this;
    }


    /**
     * @param float $opacity Between .1 (very transparent) to .9 (almost opaque).
     * @return Watermark
     */
    public function setOpacity(float $opacity): static
    {
        $this->options['opacity'] = $opacity;

        if ($this->options['opacity'] < 0 || $this->options['opacity'] > 1) {
            throw new InvalidArgumentException('Opacity should be float between 0 to 1!');
        }

        return $this;
    }

    /**
     * @param int $rotate Degree of rotation
     * @return Watermark
     */
    public function setRotate(int $rotate): static
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

    /**
     * @param string $filePath
     * @throws InvalidArgumentException
     */
    private function ensureExists(string $filePath): void
    {
        if (! file_exists($filePath)) {
            throw new InvalidArgumentException("The specified file $filePath was not found!");
        }
    }

    /**
     * @param string $dirPath
     * @throws InvalidArgumentException
     */
    private function ensureWritable(string $dirPath): void
    {
        if (!is_writable($dirPath)) {
            throw new InvalidArgumentException("The specified destination $dirPath is not writable!");
        }
    }
}
