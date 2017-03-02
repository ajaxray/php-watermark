<?php
/**
 * Created by PhpStorm.
 * User: ajaxray
 * Date: 3/2/17
 * Time: 10:20 PM
 */

namespace Ajaxray\PHPWatermark;

// https://www.sitepoint.com/adding-text-watermarks-with-imagick/
// https://www.sitepoint.com/watermarking-images/

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
    const PRINT_STYLE_DARK = 1;
    const PRINT_STYLE_LIGHT = 2;
    const PRINT_STYLE_BEVEL = 3;

    private $position = 'Center';
    private $offsetX = 5;
    private $offsetY = 5;
    private $tiled = false;

    /**
     * Font name. Should be one of the list displayed by `convert -list font` command
     *
     * @var string
     */
    private $font = 'Arial';
    private $fontSize = 12;
    private $opacity = 0.4;
    private $rotate = 0;
    private $style = self::PRINT_STYLE_BEVEL;
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

    public function withText($text, $destination)
    {
        $this->ensureExists($this->source);
        $this->ensureWritable(dirname($destination));

        exec($this->buildTextMarkCommand($text, $destination), $output, $returnCode);
        return (empty($output) && $returnCode == 0);
    }

    public function buildTextMarkCommand($text, $destination)
    {
        $text = escapeshellarg($text);
        $destination = escapeshellarg($destination);

        $anchor = 'gravity '. $this->getPosition();
        $rotate = ($this->getRotate() == '0')? '' : "rotate {$this->getRotate()}";

        $font = "-pointsize {$this->getFontSize()} -font {$this->getFont()}";
        $colorLight = "fill \"rgba\\(255,255,255,{$this->getOpacity()}\\)\"";
        $colorDark = "fill \"rgba\\(0,0,0,{$this->getOpacity()}\\)\"";

        $offset = $this->getOffset();
        $offsetLight = "{$offset[0]},{$offset[1]}";
        $offsetDark = ($offset[0] + 1) .','. ($offset[1] + 1);

        if($this->isTiled()) {
            $command = "convert -size 140x80 xc:none  $font -$colorLight -$anchor -draw \"$rotate text 10,10 $text\"  miff:- ";
            $command .= " | composite -tile - {$this->source}  $destination";
        } else {
            $command = "convert {$this->getSource()}  $font -draw \"$rotate $anchor $colorLight text $offsetLight $text $colorDark text $offsetDark $text\" $destination";
        }

        return $command;
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
            $message = "The specified destination directory $dirPath is not writable!";
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

/**
 * Image option
 * - png image
 * - position
 * - transparency %
 * - keep color (watermark/dissolve)
 * - tile
 *
 * Text Options
 * - text
 * - position
 * - font
 * - fill (color)
 * - transparency
 */
/**
=> Normal Text
$ convert sample1.jpg -pointsize 100 -font Arial -fill rgba\(0,0,0,0.4\) -gravity center -annotate +0+0 \"DocuDEX\" transparent_text_1.jpg
$ convert transparent_text_1.jpg -pointsize 100 -font Arial -fill rgba\(255,255,255,0.4\) -gravity center -annotate +2+2 \"DocuDEX\" transparent_text_2.jpg

=>Tile
convert -size 140x80 xc:none -fill grey -gravity NorthWest -draw "text 10,10 'Copyright'" -gravity SouthEast -draw "text 5,15 'Copyright'" miff:- | composite -tile - sample1.jpg  result.png

=> tile with rotate
convert -size 140x80 xc:none -fill "rgba(0,0,0,.4)" -pointsize 24 -gravity NorthWest -draw "rotate 15 text 10,10 'Copyright'" -gravity SouthEast -draw "rotate 15 text 5,15 'Copyright'"  miff:- | composite -tile - sample1.jpg  result.png
=> with Image
composite -gravity center -dissolve 30% -tile logo.png sample.jpg sample_w.jpg

=> Try PDF
convert -density 150 -strip -interlace JPEG -compress JPEG -quality 60 -sharpen 0x1.0 -trim -fill grey -font Arial-Bold -pointsize 10 -gravity South -layers composite -annotate +0+0 'ITU Mustafa Inan Kutuphanesi' special.pdf special_1.pdf

 *
 *
 *
 */

