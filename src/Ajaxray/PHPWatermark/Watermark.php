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
    const IMG_STYLE_DISSOLVE = 1;
    const IMG_STYLE_COLORLESS = 2;
    const TEXT_STYLE_BEVEL = 1;
    const TEXT_STYLE_DARK = 2;
    const TEXT_STYLE_LIGHT = 3;

    const PATTERN_MIME_IMAGE = '/^image\/\w{1,4}$/';
    const PATTERN_MIME_PDF = '/^application\/(x\-)?pdf$/';

    private $options = [
        'position' => 'Center',
        'offsetX' => 0,
        'offsetY' => 0,
        'tiled' => false,
        'tileSize' => [100, 100],
        'font' => 'Arial',
        'fontSize' => 24,
        'opacity' => 0.3,
        'rotate' => 0,
        'style' => 1, // IMG_STYLE_DISSOLVE or TEXT_STYLE_BEVEL
    ];

    private $source;
    private $commander;
    private $debug = false;

    /**
     * Watermark constructor.
     *
     * @param string $source Source Image
     */
    public function __construct($source)
    {
        $this->source = $source;
        $this->commander = $this->getCommandBuilder($source);

        return $this;
    }

    public function withText($text, $writeTo = null)
    {
        $destination = $writeTo ?: $this->source;
        $this->ensureWritable(($writeTo ? dirname($destination) : $destination));

        if($this->debug) {
            return $this->commander->getTextMarkCommand($text, $destination, $this->options);
        } else {
            $output = $returnCode = null;
            exec($this->commander->getTextMarkCommand($text, $destination, $this->options), $output, $returnCode);
            return (empty($output) && $returnCode === 0);
        }
    }

    public function withImage($marker, $writeTo = null)
    {
        $destination = $writeTo ?: $this->source;
        $this->ensureExists($marker);
        $this->ensureWritable(($writeTo ? dirname($destination) : $destination));

        if($this->debug) {
            return $this->commander->getImageMarkCommand($marker, $destination, $this->options);
        } else {
            $output = $returnCode = null;
            exec($this->commander->getImageMarkCommand($marker, $destination, $this->options), $output, $returnCode);
            return (empty($output) && $returnCode === 0);
        }
    }

    /**
     * Factory for choosing CommandBuilder
     *
     * @param $sourcePath
     * @return CommandBuilders\ImageCommandBuilder|CommandBuilders\PDFCommandBuilder
     */
    protected function getCommandBuilder($sourcePath)
    {
        $this->ensureExists($this->source);
        $mimeType = mime_content_type($sourcePath);

        if (preg_match(self::PATTERN_MIME_IMAGE, $mimeType)) {
            return new CommandBuilders\ImageCommandBuilder($sourcePath);
        } elseif (preg_match(self::PATTERN_MIME_PDF, $mimeType)) {
            return new CommandBuilders\PDFCommandBuilder($sourcePath);
        } else {
            throw new \InvalidArgumentException("The source file type $mimeType is not supported");
        }
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

        // @TODO : stretch to % of image or % of self
        // @TODO : Gap/offset between image tiles
        return "composite -$anchor -$offset -$rotate -$opacity $tile $marker $source $destination";
    }

    /**
     * @return string
     */
    private function getSource()
    {
        return escapeshellarg($this->source);
    }

    /**
     * @param string $position  One of Watermark::POSITION_* constants
     * @return Watermark
     */
    public function setPosition($position)
    {
        if(in_array($position, $this->supportedPositionList())) {
            $this->options['position'] = $position;
        } else {
            throw new \InvalidArgumentException("Position $position is not supported! Use Watermark::POSITION_* constants.");
        }

        return $this;
    }

    /**
     * @param int $offsetX
     * @param int $offsetY
     *
     * @return Watermark
     */
    public function setOffset($offsetX, $offsetY)
    {
        $this->options['offsetX'] = intval($offsetX);
        $this->options['offsetY'] = intval($offsetY);

        return $this;
    }

    /**
     * @param int $style
     * @return Watermark
     */
    public function setStyle($style)
    {
        // @TODO : Check if in valid styles
        $this->options['style'] = $style;

        return $this;
    }

    /**
     * @param bool $tiled
     * @return Watermark
     */
    public function setTiled($tiled = true)
    {
        $this->options['tiled'] = boolval($tiled);

        return $this;
    }

    /**
     * Size of tile box.
     *
     * @param $width
     * @param $height
     *
     * @return Watermark
     */
    public function setTileSize($width, $height)
    {
        $this->options['tileSize'] = [intval($width), intval($height)];

        return $this;
    }

    /**
     * Font name. Should be one of the list displayed by `convert -list font` command
     *
     * @param string $font
     * @return Watermark
     */
    public function setFont($font)
    {
        $this->options['font'] = $font;

        return $this;
    }

    /**
     * @param int $fontSize
     * @return Watermark
     */
    public function setFontSize($fontSize)
    {
        $this->options['fontSize'] = intval($fontSize);

        return $this;
    }


    /**
     * @param float $opacity Between .1 (very transparent) to .9 (almost opaque).
     * @return Watermark
     */
    public function setOpacity($opacity)
    {
        $this->options['opacity'] = floatval($opacity);

        return $this;
    }

    /**
     * @param int $rotate Degree of rotation
     */
    public function setRotate($rotate)
    {
        $this->options['rotate'] = intval($rotate);
    }

    /**
     * @param bool $debug
     * @return Watermark
     */
    public function setDebug($debug)
    {
        $this->debug = boolval($debug);

        return $this;
    }

    final public function supportedPositionList()
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

}
