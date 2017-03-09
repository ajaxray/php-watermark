<?php
/**
 * Created by PhpStorm.
 * User: ajaxray
 * Date: 3/5/17
 * Time: 11:24 PM
 */

namespace Ajaxray\PHPWatermark\CommandBuilders;


class ImageCommandBuilder extends AbstractCommandBuilder
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
        list($source, $destination) = $this->prepareContext($output, $options);
        $marker = escapeshellarg($markerImage);

        $anchor = $this->getAnchor();
        $offset = $this->getImageOffset();

        $tile = $this->getTile();
        $opacity = $this->getImageOpacity();

        // @TODO : stretch to % of image or % of self
        // @TODO : Gap/offset between image tiles
        return "composite -$anchor -$offset -$opacity $tile $marker $source $destination";
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

        $draw = " -draw \"$rotate $anchor $light text $offsetLight $text $dark text $offsetDark $text\" ";

        // @TODO : Fix issue with single quote
        if($this->isTiled()) {
            $size = $this->getTextTileSize();
            $command = "convert $size xc:none  $font -$anchor $draw miff:- ";
            $command .= " | composite -tile - $source  $destination";
        } else {
            $command = "convert $source $font $draw $destination";
        }

        return $command;
    }
}