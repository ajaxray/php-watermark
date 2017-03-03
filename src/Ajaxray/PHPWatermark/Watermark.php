<?php
/**
 * Created by PhpStorm.
 * User: ajaxray
 * Date: 3/2/17
 * Time: 10:20 PM
 */

namespace Ajaxray\PHPWatermark;

// https://www.sitepoint.com/adding-text-watermarks-with-imagick/
// http://www.imagemagick.org/Usage/annotating/#watermarking

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
    const POSITION_RIGHT = 'West';
    const POSITION_CENTER = 'Center';
    const POSITION_LEFT = 'East';
    const POSITION_BOTTOM_LEFT = 'SouthWest';
    const POSITION_BOTTOM = 'South';
    const POSITION_BOTTOM_RIGHT = 'SouthEast';

    // @TODO : Option to change style
    const IMG_STYLE_COLORLESS = 1;
    const IMG_STYLE_DISSOLVE = 2;
    const TEXT_STYLE_DARK = 1;
    const TEXT_STYLE_LIGHT = 2;
    const TEXT_STYLE_BEVEL = 3;

    private $position = 'Center';
    private $offsetX = 0;
    private $offsetY = 0;
    private $tiled = false;
    private $tileSize = [100, 100];

    /**
     * Font name. Should be one of the list displayed by `convert -list font` command
     *
     * @var string
     */
    private $font = 'Arial';
    private $fontSize = 12;
    private $opacity = 0.4;
    private $rotate = 0;
    private $source;

    /**
     * Watermark constructor.
     * @param string $source  Source Image
     */
    public function __construct($source)
    {
        $this->source = $source;

        return $this;
    }

    public function withText($text, $writeTo = null)
    {
        $destination = $writeTo ?: $this->source;
        $this->ensureExists($this->source);
        $this->ensureWritable(($writeTo ? dirname($destination) : $destination));

        exec($this->buildTextMarkCommand($text, $destination), $output, $returnCode);
        return (empty($output) && $returnCode == 0);
    }

    public function withImage($marker, $writeTo = null)
    {
        $destination = $writeTo ?: $this->source;
        $this->ensureExists($this->source);
        $this->ensureExists($marker);
        $this->ensureWritable(($writeTo ? dirname($destination) : $destination));

        exec($this->buildImageMarkCommand($marker, $destination), $output, $returnCode);
        return (empty($output) && $returnCode == 0);
    }

    public function buildTextMarkCommand($text, $destination)
    {
        $source = $this->getSource();
        $destination = escapeshellarg($destination);
        $text = escapeshellarg($text);

        $anchor = 'gravity '. $this->getPosition();
        $rotate = ($this->getRotate() == '0')? '' : "rotate {$this->getRotate()}";

        $font = "-pointsize {$this->getFontSize()} -font {$this->getFont()}";
        $colorLight = "fill \"rgba\\(255,255,255,{$this->getOpacity()}\\)\"";
        $colorDark = "fill \"rgba\\(0,0,0,{$this->getOpacity()}\\)\"";

        $offset = $this->getOffset();
        $offsetLight = "{$offset[0]},{$offset[1]}";
        $offsetDark = ($offset[0] + 1) .','. ($offset[1] + 1);

        $draw = " -draw \"$rotate $anchor $colorLight text $offsetLight $text $colorDark text $offsetDark $text \" ";

        // @TODO : Fix issue with single quote
        if($this->isTiled()) {
            $size = "-size ". implode('x', $this->getTileSize());
            $command = "convert $size xc:none  $font -$anchor $draw miff:- ";
            $command .= " | composite -tile - $source  $destination";
        } else {
            $command = "convert $source $font $draw $destination";
        }

        return $command;
    }

    public function buildImageMarkCommand($marker, $destination)
    {
        $source = $this->getSource();
        $destination = escapeshellarg($destination);
        $marker = escapeshellarg($marker);

        $anchor = 'gravity '. $this->getPosition();
        $rotate = ($this->getRotate() == '0')? '' : "rotate {$this->getRotate()}";

        $offsetArr = $this->getOffset();
        $offset = "geometry +{$offsetArr[0]}+{$offsetArr[1]}";
        $tile = $this->isTiled() ? '-tile' : '';
        //$opacity = 'dissolve '. ($this->getOpacity() * 100) .'%';
        $opacity = 'watermark '. ($this->getOpacity() * 100);

        // @TODO : Gap/offset between image tiles
        return "composite -$anchor -$offset -$rotate -$opacity $tile $marker $source $destination";
    }

    private function ensureExists($filePath)
    {
        if (! file_exists($filePath)) {
            $message = "The specified source file $filePath was not found!";
            throw new \RuntimeException($message);
            // @TODO : Create SourceNotFoundException
        }
    }

    private function ensureWritable($dirPath)
    {
        if (! is_writable($dirPath)) {
            $message = "The specified destination $dirPath is not writable!";
            throw new \RuntimeException($message);
            // @TODO : Create DestNotWritableException
        }
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     * @return Watermark
     */
    public function setPosition($position)
    {
        // @TODO : Check if in accepted values
        $this->position = $position;

        return $this;
    }

    /**
     * @return array
     */
    public function getOffset()
    {
        return [$this->offsetX, $this->offsetY];
    }

    /**
     * @param int $offsetX
     * @param int $offsetY
     *
     * @return Watermark
     */
    public function setOffset($offsetX, $offsetY)
    {
        $this->offsetX = intval($offsetX);
        $this->offsetY = intval($offsetY);

        return $this;
    }

    /**
     * @return int
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param int $style
     * @return Watermark
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTiled()
    {
        return $this->tiled;
    }

    /**
     * @param bool $tiled
     * @return Watermark
     */
    public function setTiled($tiled = true)
    {
        $this->tiled = $tiled;

        return $this;
    }

    /**
     * @param $width
     * @param $height
     *
     * @return Watermark
     */
    public function setTileSize($width, $height)
    {
        $this->tileSize = [intval($width), intval($height)];

        return $this;
    }

    /**
     * @return array
     */
    public function getTileSize()
    {
        return $this->tileSize;
    }

    /**
     * @return string
     */
    public function getFont()
    {
        return $this->font;
    }

    /**
     * @param string $font
     * @return Watermark
     */
    public function setFont($font)
    {
        $this->font = escapeshellarg($font);

        return $this;
    }

    /**
     * @return string
     */
    public function getFontSize()
    {
        return escapeshellarg($this->fontSize);
    }

    /**
     * @param int $fontSize
     * @return Watermark
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getOpacity()
    {
        return floatval($this->opacity);
    }

    /**
     * @param float $opacity
     * @return Watermark
     */
    public function setOpacity($opacity)
    {
        $this->opacity = $opacity;

        return $this;
    }

    /**
     * @return string
     */
    public function getRotate()
    {
        return escapeshellarg($this->rotate);
    }

    /**
     * @param int $rotate
     */
    public function setRotate($rotate)
    {
        $this->rotate = $rotate;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return escapeshellarg($this->source);
    }
}
