<?php
/**
 * Created by PhpStorm.
 * User: Anis Ahmad <anis.programmer@gmail.com>
 * Date: 3/5/17
 * Time: 11:21 PM
 */

namespace Ajaxray\PHPWatermark\CommandBuilders;


abstract class AbstractCommandBuilder
{
    protected $options;

    /**
     * @var string Source file path
     */
    protected $source;

    /**
     * AbstractCommandBuilder constructor.
     *
     * @param string $source The source file to watermark on
     */
    public function __construct($source)
    {
        $this->source = $source;
    }


    /**
     * Build the imagemagick shell command for watermarking with Image
     *
     * @param string $markerImage The image file to watermark with
     * @param string $output The watermarked output file
     * @param array $options
     * @return string
     */
    abstract public function getImageMarkCommand($markerImage, $output, array $options);

    /**
     * Build the imagemagick shell command for watermarking with Text
     *
     * @param string $text The text content to watermark with
     * @param string $output The watermarked output file
     * @param array $options
     * @return string
     */
    abstract public function getTextMarkCommand($text, $output, array $options);

    /**
     * @return string
     */
    protected function getSource()
    {
        return escapeshellarg($this->source);
    }

    /**
     * @param $output
     * @param array $options
     * @return array
     */
    protected function prepareContext($output, array $options)
    {
        $this->options = $options;
        return array($this->getSource(), escapeshellarg($output));
    }

    protected function getAnchor()
    {
        return 'gravity '. $this->options['position'];
    }

    /**
     * @return array
     */
    protected function getOffset()
    {
        return [$this->options['offsetX'], $this->options['offsetY']];
    }

    /**
     * @return int
     */
    protected function getStyle()
    {
        return $this->options['style'];
    }

    /**
     * @return bool
     */
    protected function isTiled()
    {
        return $this->options['tiled'];
    }

    /**
     * @return string
     */
    protected function getTextTileSize()
    {
        return "-size ".implode('x', $this->options['tileSize']);
    }

    /**
     * @return string
     */
    protected function getFont()
    {
        return '-pointsize '.intval($this->options['fontSize']).
            ' -font '.escapeshellarg($this->options['font']);
    }

    protected function getDuelTextOffset()
    {
        $offset = $this->getOffset();
        return [
            "{$offset[0]},{$offset[1]}",
            ($offset[0] + 1) .','. ($offset[1] + 1),
        ];
    }

    protected function getImageOffset()
    {
        $offsetArr = $this->getOffset();
        return "geometry +{$offsetArr[0]}+{$offsetArr[1]}";
    }

    /**
     * @return float
     */
    protected function getOpacity()
    {
        return $this->options['opacity'];
    }

    /**
     * @return string
     */
    protected function getTile()
    {
        return empty($this->isTiled()) ? '' : '-tile';
    }
}
