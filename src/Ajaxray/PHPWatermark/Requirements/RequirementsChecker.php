<?php declare(strict_types=1);

namespace Ajaxray\PHPWatermark\Requirements;

class RequirementsChecker
{
    public function ensureImagemagickInstallation(): bool
    {
        exec("convert -version", $out, $returnCode);

        if ($returnCode !== 0) {
            throw new \BadFunctionCallException("ImageMagick not found in this system.");
        }

        return true;
    }
}
