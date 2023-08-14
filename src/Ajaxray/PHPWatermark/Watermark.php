<?php declare(strict_types=1);

namespace Ajaxray\PHPWatermark;

use Ajaxray\PHPWatermark\CommandBuilders\CommandBuilderFactory;
use Ajaxray\PHPWatermark\CommandBuilders\WatermarkCommandBuilderInterface;

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
    private WatermarkCommandBuilderInterface $commandBuilder;

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
        'fontcolor' => '#ffffff',
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
     * Font color. Should to be in rgb or rgba format 
     *
     * @param string $fontColor
     * @return Watermark
     * Added by shqawe@gmail.com
     */
    public function setFontColor($fontColor)
    {
        $this->options['fontcolor'] = $this->_prepareColorForWaterMark($fontColor);
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

     /**
     *  Prepare color for watermark command line
     */
    private function _prepareColorForWaterMark($color)
    {
        $hexColor = $this->_colorToRGB($color);

        $opecity = $this->options['opacity'];

        $rgba = "rgba\\(" . $hexColor[0] . "," . $hexColor[1] . "," . $hexColor[2] . "," . $opecity . "\\)";
        return $rgba;
    }
    
    private function _colorToRGB($hex)
    {
        $hex = strtolower($hex);

        if (strpos($hex, 'rgba') !== false) {
            preg_match('/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/i', $hex, $rgb);

            if ($rgb) {
                if (!empty($rgb[4]) && \XF::options()->up_wm_opecity === 0) {
                    $this->setOpacity(intval(127 - 127 * $rgb[4]));
                }

                return [$rgb[1], $rgb[2], $rgb[3]];
            }
        }

        if (strpos($hex, 'rgb') !== false) {
            preg_match('/^rgb\(\s*(\d+%?)\s*,\s*(\d+%?)\s*,\s*(\d+%?)\s*\)$/i', $hex, $rgb);

            if ($rgb) {
                return [$rgb[1], $rgb[2], $rgb[3]];
            }
        } else {
            $hex = str_replace('#', '', $hex);

            if (utf8_strlen($hex) == 3) {
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }

            return [$r, $g, $b];
        }

        return [0, 0, 0];
    }
}
