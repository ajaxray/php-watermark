<?php
/**
 * Created by PhpStorm.
 * User: ajaxray
 * Date: 3/5/17
 * Time: 11:24 PM
 */

namespace Ajaxray\PHPWatermark\CommandBuilders;


class PDFCommandBuilder extends AbstractCommandBuilder
{

    /**
     * Build the imagemagick shell command for watermarking with Image
     *
     * @param string $markerImage The image file to watermark with
     * @param string $output The watermarked output file
     * @param array $options
     * @return string
     */
    function getImageMarkCommand($markerImage, $output, array $options)
    {

        return "convert php.png -alpha set -channel A -evaluate set 30%  miff:- | convert -density 100 test.pdf null: - -gravity center -quality 100  -compose multiply -layers composite destination.pdf";
    }

    /**
     * Build the imagemagick shell command for watermarking with Text
     *
     * @param string $text The text content to watermark with
     * @param string $output The watermarked output file
     * @param array $options
     * @return string
     */
    function getTextMarkCommand($text, $output, array $options)
    {
        list($source, $destination) = $this->prepareContext($output, $options);
        $text = escapeshellarg($text);

        $anchor = $this->getAnchor();
        $rotate = $this->getRotate();
        $font = $this->getFont();
        list($light, $dark) = $this->getDuelTextColor();
        list($offsetLight, $offsetDark) = $this->getDuelTextOffset();

        return "convert $source $anchor -quality 100 -density 100 $font -$light -annotate {$rotate}{$offsetLight} $text -$dark -annotate {$rotate}{$offsetDark} $text  $destination";
        //With rotate: convert examples/pdf/dark.pdf  -gravity NorthEast -quality 100 -density 100 -pointsize 24 -fill "rgba(255,255,255, .4)" -annotate 345x345+20+51 'ajaxray.com' -fill "rgba(0,0,0, .4)"  -annotate 345x345+21+51 'ajaxray.com' 'examples/pdf/test.pdf'
    }

    protected function getDuelTextOffset()
    {
        $offset = $this->getOffset();
        return [
            "+{$offset[0]}+{$offset[1]}",
            '+'.($offset[0] + 1) .'+'. ($offset[1] + 1),
        ];
    }

    protected function getRotate()
    {
        return empty($this->options['rotate']) ? '' : "{$this->options['rotate']}x{$this->options['rotate']}";
    }
}