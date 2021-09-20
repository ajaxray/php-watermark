<?php declare(strict_types=1);

namespace Ajaxray\PHPWatermark\CommandBuilders;

use Ajaxray\PHPWatermark\Requirements\RequirementsChecker;

abstract class AbstractCommandBuilder
{
    protected array $options;
    protected string $source;

    public function __construct(string $source)
    {
        $this->source = $source;

        (new RequirementsChecker())->ensureImagemagickInstallation();
    }

    protected function getSource(): string
    {
        return escapeshellarg($this->source);
    }

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

    protected function getFont(): string
    {
        return '-pointsize ' . intval($this->options['fontSize']) .
            ' -font ' . escapeshellarg($this->options['font']);
    }

    protected function getDuelTextOffset(): array
    {
        $offset = $this->getOffset();

        return [
            "{$offset[0]},{$offset[1]}",
            ($offset[0] + 1) . ',' . ($offset[1] + 1),
        ];
    }

    protected function getImageOffset(): string
    {
        $offsetArr = $this->getOffset();

        return "geometry +{$offsetArr[0]}+{$offsetArr[1]}";
    }

    protected function getOpacity(): float
    {
        return $this->options['opacity'];
    }

    protected function getTile(): string
    {
        return empty($this->isTiled()) ? '' : '-tile';
    }
}
