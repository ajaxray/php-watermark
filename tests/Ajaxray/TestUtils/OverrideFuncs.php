<?php
declare(strict_types=1);
// Mechanism for mocking some built in functions

namespace Ajaxray\PHPWatermark;

$GLOBALS['mockGlobalFunctions'] = false;
$GLOBALS['lastExecCommand'] = null;

function file_exists(string $path)
{
    global $mockGlobalFunctions;

    if (isset($mockGlobalFunctions) && $mockGlobalFunctions === true) {
        return true;
    } else {
        return call_user_func_array('\file_exists', func_get_args());
    }
}

function is_writable(string $path)
{
    global $mockGlobalFunctions;

    if (isset($mockGlobalFunctions) && $mockGlobalFunctions === true) {
        return true;
    } else {
        return call_user_func_array('\is_writable', func_get_args());
    }
}

if(! function_exists('Ajaxray\PHPWatermark\exec')) {
    function exec($command, $output, $returnCode)
    {
        global $mockGlobalFunctions, $lastExecCommand;

        if (isset($mockGlobalFunctions) && $mockGlobalFunctions === true) {
            $lastExecCommand = func_get_arg(0);
            return 0;
        } else {
            return call_user_func_array('\exec', func_get_args());
        }
    }
}

namespace Ajaxray\PHPWatermark\CommandBuilders;
function mime_content_type($path)
{
    global $mockGlobalFunctions;

    if (isset($mockGlobalFunctions) && $mockGlobalFunctions === true) {
        if(preg_match('/(png)|(jpe?g)|(gif)/', $path, $match)) {
            return 'image/'. $match[0];
        } elseif (preg_match('/(pdf)|(x\-pdf)/', $path, $match)) {
            return 'application/'. $match[0];
        }
        return 'no-pdf/no-image';
    } else {
        return call_user_func_array('\mime_content_type', func_get_args());
    }
}