<?php
declare(strict_types=1);

namespace Ajaxray\PHPWatermark\CommandBuilders;

class PDFCommandBuilder extends AbstractCommandBuilder implements WatermarkCommandBuilder
{

    /**
     * Build the imagemagick shell command for watermarking with Image
     *
     * @param string $markerImage The image file to watermark with
     * @param string $output The watermarked output file
     * @param array $options
     * @return string
     */
    public function getImageMarkCommand(string $markerImage, string $output, array $options): string
    {
        list($source, $destination) = $this->prepareContext($output, $options);
        $marker = escapeshellarg($markerImage);

        $opacity = $this->getMarkerOpacity();
        $anchor = $this->getAnchor();
        $offset = $this->getImageOffset();

        return "convert $marker $opacity  miff:- | convert -density 100 $source null: - -$anchor -$offset -quality 100 -compose multiply -layers composite $destination";
    }

    /**
     * Build the imagemagick shell command for watermarking with Text
     *
     * @param string $text The text content to watermark with
     * @param string $output The watermarked output file
     * @param array $options
     * @return string
     */
    public function getTextMarkCommand(string $text, string $output, array $options): string
    {
        list($source, $destination) = $this->prepareContext($output, $options);
        $text = escapeshellarg($text);

        $anchor = $this->getAnchor();
        $rotate = $this->getRotate();
        $font = $this->getFont();

        list($light, $dark) = $this->getDuelTextColor();
        list($offsetLight, $offsetDark) = $this->getDuelTextOffset();

        return "convert $source -$anchor -quality 100 -density 100 $font -$light -annotate {$rotate}{$offsetLight} $text -$dark -annotate {$rotate}{$offsetDark} $text  $destination";
    }

    private function getMarkerOpacity()
    {
        $opacity = $this->getOpacity() * 100;
        return "-alpha set -channel A -evaluate set {$opacity}%";
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

    protected function getDuelTextColor()
    {
        return [
            "fill \"rgba(255,255,255,{$this->getOpacity()})\"",
            "fill \"rgba(0,0,0,{$this->getOpacity()})\"",
        ];
    }
}
