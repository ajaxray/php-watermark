<?php
/**
 * Created by PhpStorm.
 * User: ajaxray
 * Date: 3/5/17
 * Time: 4:56 PM
 */
namespace Ajaxray\PHPWatermark\Tests;

use Ajaxray\PHPWatermark\CommandBuilders\ImageCommandBuilder;
use Ajaxray\PHPWatermark\CommandBuilders\PDFCommandBuilder;
use Ajaxray\PHPWatermark\Watermark;
use Ajaxray\TestUtils\NonPublicAccess;

class WatermarkTest extends \PHPUnit_Framework_TestCase
{
    use NonPublicAccess;

    protected function setUp()
    {
        global $mockGlobalFunctions;
        $mockGlobalFunctions = true;
    }

    public function testLoadingImageCommandBuilderForImages()
    {
        $watermark = new Watermark('path/to/image/file.jpeg');
        $this->assertInstanceOf(ImageCommandBuilder::class, $this->invokeProperty($watermark, 'commander'));

        $watermark = new Watermark('path/to/image/file.jpg');
        $this->assertInstanceOf(ImageCommandBuilder::class, $this->invokeProperty($watermark, 'commander'));

        $watermark = new Watermark('path/to/file.png');
        $this->assertInstanceOf(ImageCommandBuilder::class, $this->invokeProperty($watermark, 'commander'));
    }

    public function testLoadingPDFCommandBuilderForPDFs()
    {
        $watermark = new Watermark('path/to/pdf/file.pdf');
        $this->assertInstanceOf(PDFCommandBuilder::class, $this->invokeProperty($watermark, 'commander'));

        $watermark = new Watermark('path/to/x-pdf/file.pdf');
        $this->assertInstanceOf(PDFCommandBuilder::class, $this->invokeProperty($watermark, 'commander'));
    }

    public function testThrowsExceptionForUnsupportedSourceTypes()
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The source file type no-pdf/no-image is not supported');

        new Watermark('path/to/test.html');
    }

    public function testWithTextExecutesShellCommand()
    {
        global $lastExecCommand;
        $watermark = new Watermark('path/to/file.png');
        $watermark->withText('CONFIDENTIAL', 'output.jpg');

        $this->assertContains('convert', $lastExecCommand);
        $this->assertContains('CONFIDENTIAL', $lastExecCommand);
        $this->assertContains('path/to/file.png', $lastExecCommand);
        $this->assertContains('output.jpg', $lastExecCommand);
    }

    // @TODO : Test getters functions to check if converting them to expected format

}


// Mechanism for mocking some built in functions

namespace Ajaxray\PHPWatermark;

$GLOBALS['mockGlobalFunctions'] = false;
$GLOBALS['lastExecCommand'] = null;

function file_exists($path)
{
    global $mockGlobalFunctions;

    if (isset($mockGlobalFunctions) && $mockGlobalFunctions === true) {
        return true;
    } else {
        return call_user_func_array('\file_exists', func_get_args());
    }
}

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

function exec($command, $output, $returnCode)
{
    global $mockGlobalFunctions, $lastExecCommand;

    if (isset($mockGlobalFunctions) && $mockGlobalFunctions === true) {
        $lastExecCommand = func_get_arg(0);
    } else {
        return call_user_func_array('\exec', func_get_args());
    }
}

