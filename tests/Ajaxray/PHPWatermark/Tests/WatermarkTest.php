<?php
/**
 * Created by PhpStorm.
 * User: Anis Ahmad <anis.programmer@gmail.com>
 * Date: 3/5/17
 * Time: 4:56 PM
 */
namespace Ajaxray\PHPWatermark\Tests;

use Ajaxray\PHPWatermark\CommandBuilders\ImageCommandBuilder;
use Ajaxray\PHPWatermark\CommandBuilders\PDFCommandBuilder;
use Ajaxray\PHPWatermark\Watermark;
use Ajaxray\TestUtils\NonPublicAccess;
use PHPUnit\Framework\TestCase;

class WatermarkTest extends TestCase
{
    use NonPublicAccess;

    protected function setUp()
    {
        global $mockGlobalFunctions;

        $mockGlobalFunctions = true;
    }

    protected function tearDown()
    {
        global $mockGlobalFunctions, $lastExecCommand;

        $mockGlobalFunctions = false;
        $lastExecCommand = null;
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

    public function testWatermarkWithTextExecutesShellCommand()
    {
        global $lastExecCommand;
        $watermark = new Watermark('path/to/file.png');
        $watermark->withText('CONFIDENTIAL', 'output.jpg');

        $this->assertContains('convert', $lastExecCommand);
        $this->assertContains('CONFIDENTIAL', $lastExecCommand);
        $this->assertContains('path/to/file.png', $lastExecCommand);
        $this->assertContains('output.jpg', $lastExecCommand);
    }

    public function testWatermarkWithImageExecutesShellCommand()
    {
        global $lastExecCommand;
        $watermark = new Watermark('path/to/file.jpg');
        $watermark->withImage('path/company-logo.png', 'output.jpg');

        $this->assertContains('composite', $lastExecCommand);
        $this->assertContains('path/company-logo.png', $lastExecCommand);
        $this->assertContains('path/to/file.jpg', $lastExecCommand);
        $this->assertContains('output.jpg', $lastExecCommand);
    }

    public function testThrowsExceptionOnInvalidPosition()
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('Position SOMEWHERE_ELSE is not supported! Use Watermark::POSITION_* constants.');

        $watermark = new Watermark('path/to/source.jpg');
        $watermark->setPosition('SOMEWHERE_ELSE');
    }

    public function testOffsetCastingToInt()
    {
        $watermark = new Watermark('path/to/file.jpg');
        $watermark->setOffset('220', '10cm');

        $options = $this->invokeProperty($watermark, 'options');
        $this->assertTrue(is_int($options['offsetX']));
        $this->assertTrue(is_int($options['offsetY']));
        $this->assertEquals(10, $options['offsetY']);
    }

    public function testOpacityCastingToFloat()
    {
        $watermark = new Watermark('path/to/file.jpg');
        $watermark->setOpacity('.5');

        $options = $this->invokeProperty($watermark, 'options');
        $this->assertTrue(is_float($options['opacity']));
    }

    public function testThrowsExceptionIfOpacityIsNotBetween0To1()
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('Opacity should be float between 0 to 1!');

        $watermark = new Watermark('path/to/file.jpg');
        $watermark->setOpacity(2);
    }

    public function testRotationCastingToAbsoluteInt()
    {
        $watermark = new Watermark('path/to/file.jpg');
        $watermark->setRotate('-5 degree');

        $options = $this->invokeProperty($watermark, 'options');
        $this->assertTrue(is_int($options['rotate']));
        $this->assertEquals(5, $options['rotate']);
    }

    public function testSetTiledCastingToBoolean()
    {
        $watermark = new Watermark('path/to/file.jpg');
        $watermark->setTiled('y');

        $options = $this->invokeProperty($watermark, 'options');
        $this->assertTrue(is_bool($options['tiled']));
        $this->assertSame(true, $options['tiled']);
    }

    public function testThrowsExceptionIfSourceNotFound()
    {
        global $mockGlobalFunctions;
        $mockGlobalFunctions = false;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The specified file path/to/file.jpg was not found!');

        new Watermark('path/to/file.jpg');
    }

    public function testThrowsExceptionIfMarkerImageNotFound()
    {
        global $mockGlobalFunctions;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The specified file non/existing/marker.png was not found!');

        $watermark = new Watermark('path/to/file.jpg');

        $mockGlobalFunctions = false;
        $watermark->withImage('non/existing/marker.png');
    }

    public function testThrowsExceptionIfDestinationNotWritable()
    {
        global $mockGlobalFunctions;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The specified destination non/existing is not writable!');

        $watermark = new Watermark('path/to/file.jpg');

        $mockGlobalFunctions = false;
        $watermark->withText('text', 'non/existing/output.jpg');
    }

    public function testThrowsExceptionIfSourceNotImageOrPDF()
    {
        global $mockGlobalFunctions;
        $mockGlobalFunctions = false;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The source file type text/x-php is not supported.');

        new Watermark(__FILE__);
    }

    public function testCommandNotExecutedIfDebugEnabled()
    {
        global $lastExecCommand;

        $watermark = new Watermark('path/to/file.jpg');
        $watermark->setDebug();
        $watermark->withText('Whatever!');

        $this->assertNull($lastExecCommand);
    }

    public function testCommandReturnedIfDebugEnabled()
    {
        global $lastExecCommand;

        $watermark = new Watermark('path/to/file.jpg');
        $watermark->setDebug();
        $command = $watermark->withImage('path/to/logo.png');

        $this->assertContains('composit', $command);
        $this->assertContains('path/to/logo.png', $command);
    }

    public function testSetPositionOnPositionList()
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setPosition = $watermark->setPosition(Watermark::POSITION_CENTER);
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setPosition);
        $this->assertContains(Watermark::POSITION_CENTER, $options);
    }

    public function testSetStyle()
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setStyle = $watermark->setStyle(Watermark::STYLE_IMG_COLORLESS);
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setStyle);
        $this->assertContains(Watermark::STYLE_IMG_COLORLESS, $options);
    }

    public function testSetTileSize()
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setStyle = $watermark->setTileSize(200, 150);
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setStyle);
        $this->assertContains([200, 150], $options);
    }

    public function testSetFont()
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setFont = $watermark->setFont('Arial');
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setFont);
        $this->assertContains('Arial', $options);
    }

    public function testSetFontSize()
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setFontSize = $watermark->setFontSize(20);
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setFontSize);
        $this->assertContains(20, $options);
    }

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

function is_writable($path)
{
    global $mockGlobalFunctions;

    if (isset($mockGlobalFunctions) && $mockGlobalFunctions === true) {
        return true;
    } else {
        return call_user_func_array('\is_writable', func_get_args());
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
