<?php

declare(strict_types=1);

namespace Ajaxray\PHPWatermark\CommandBuilders;

use Ajaxray\PHPWatermark\Requirements\RequirementsChecker;

/**
 * AbstractCommandBuilder defines common functionalities of all CommandBuilder classes
 */
abstract class AbstractCommandBuilder
{
    protected array $options;

    /**
     * @var string Source file path
     */
    protected string $source;

    /**
     * AbstractCommandBuilder constructor.
     *
     * @param string $source The source file to watermark on
     */
    public function __construct(string $source)
    {
        $this->source = $source;

        (new RequirementsChecker())->checkImagemagickInstallation();
    }

    /**
     * @return string
     */
    protected function getSource(): string
    {
        return escapeshellarg($this->source);
    }

    /**
     * @param $output
     * @param array $options
     * @return array
     */
    protected function prepareContext($output, array $options): array
    {
        $this->options = $options;
        return array($this->getSource(), escapeshellarg($output));
    }

    protected function getAnchor(): string
    {
        return 'gravity ' . $this->options['position'];
    }

    protected function getOffset(): array
    {
        return [$this->options['offsetX'], $this->options['offsetY']];
    }

    protected function getStyle(): int
    {
        return $this->options['style'];
    }

    protected function isTiled(): bool
    {
        return $this->options['tiled'];
    }

    protected function getTextTileSize(): string
    {
        return "-size " . implode('x', $this->options['tileSize']);
    }

    /**
     * @return string
     */
    protected function getFont()
    {
        return '-pointsize ' . intval($this->options['fontSize']) .
            ' -font ' . escapeshellarg($this->options['font']);
    }

    protected function getDuelTextOffset()
    {
        $offset = $this->getOffset();
        return [
            "{$offset[0]},{$offset[1]}",
            ($offset[0] + 1) . ',' . ($offset[1] + 1),
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
