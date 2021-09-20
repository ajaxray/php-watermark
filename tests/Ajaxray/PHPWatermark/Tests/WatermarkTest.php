<?php declare(strict_types=1);

namespace Ajaxray\PHPWatermark\Tests;

use Ajaxray\PHPWatermark\CommandBuilders\ImageCommandBuilder;
use Ajaxray\PHPWatermark\CommandBuilders\PDFCommandBuilder;
use Ajaxray\PHPWatermark\Watermark;
use Ajaxray\TestUtils\NonPublicAccess;
use PHPUnit\Framework\TestCase;

class WatermarkTest extends TestCase
{
    use NonPublicAccess;

    /**
     * @see tests/Ajaxray/TestUtils/OverrideFunctions.php
     */
    protected function setUp(): void
    {
        global $mockGlobalFunctions;

        $mockGlobalFunctions = true;
    }

    protected function tearDown(): void
    {
        global $mockGlobalFunctions, $lastExecCommand;

        $mockGlobalFunctions = false;
        $lastExecCommand = null;
    }

    public function testLoadingImageCommandBuilderForImages(): void
    {
        $watermark = new Watermark('path/to/image/file.jpeg');
        $this->assertInstanceOf(ImageCommandBuilder::class, $this->invokeProperty($watermark, 'commandBuilder'));

        $watermark = new Watermark('path/to/image/file.jpg');
        $this->assertInstanceOf(ImageCommandBuilder::class, $this->invokeProperty($watermark, 'commandBuilder'));

        $watermark = new Watermark('path/to/file.png');
        $this->assertInstanceOf(ImageCommandBuilder::class, $this->invokeProperty($watermark, 'commandBuilder'));
    }

    public function testLoadingPDFCommandBuilderForPdfs(): void
    {
        $watermark = new Watermark('path/to/pdf/file.pdf');
        $this->assertInstanceOf(PDFCommandBuilder::class, $this->invokeProperty($watermark, 'commandBuilder'));

        $watermark = new Watermark('path/to/x-pdf/file.pdf');
        $this->assertInstanceOf(PDFCommandBuilder::class, $this->invokeProperty($watermark, 'commandBuilder'));
    }

    public function testThrowsExceptionForUnsupportedSourceTypes(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The source file type no-pdf/no-image is not supported');

        new Watermark('path/to/test.html');
    }

    public function testWatermarkWithTextExecutesShellCommand(): void
    {
        global $lastExecCommand;
        $watermark = new Watermark('path/to/file.png');
        $watermark->withText('CONFIDENTIAL')
            ->write('output.jpg');

        $this->assertStringContainsString('convert', $lastExecCommand);
        $this->assertStringContainsString('CONFIDENTIAL', $lastExecCommand);
        $this->assertStringContainsString('path/to/file.png', $lastExecCommand);
        $this->assertStringContainsString('output.jpg', $lastExecCommand);
    }

    public function testWatermarkWithImageExecutesShellCommand(): void
    {
        global $lastExecCommand;
        $watermark = new Watermark('path/to/file.jpg');
        $watermark->withImage('path/company-logo.png')
            ->write('output.jpg');

        $this->assertStringContainsString('composite', $lastExecCommand);
        $this->assertStringContainsString('path/company-logo.png', $lastExecCommand);
        $this->assertStringContainsString('path/to/file.jpg', $lastExecCommand);
        $this->assertStringContainsString('output.jpg', $lastExecCommand);
    }

    public function testThrowsExceptionOnInvalidPosition(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('Position SOMEWHERE_ELSE is not supported! Use Watermark::POSITION_* constants.');

        $watermark = new Watermark('path/to/source.jpg');
        $watermark->setPosition('SOMEWHERE_ELSE');
    }

    public function testThrowsExceptionIfOpacityIsNotBetween0To1(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('Opacity should be float between 0 to 1!');

        $watermark = new Watermark('path/to/file.jpg');
        $watermark->setOpacity(2);
    }

    public function testRotationCastingToAbsoluteInt(): void
    {
        $watermark = new Watermark('path/to/file.jpg');
        $watermark->setRotate(5);

        $options = $this->invokeProperty($watermark, 'options');
        $this->assertTrue(is_int($options['rotate']));
        $this->assertEquals(5, $options['rotate']);
    }

    public function testThrowsExceptionIfSourceNotFound(): void
    {
        global $mockGlobalFunctions;
        $mockGlobalFunctions = false;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The specified file path/to/file.jpg was not found!');

        new Watermark('path/to/file.jpg');
    }

    public function testThrowsExceptionIfMarkerImageNotFound(): void
    {
        global $mockGlobalFunctions;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The specified file non/existing/marker.png was not found!');

        $watermark = new Watermark('path/to/file.jpg');

        $mockGlobalFunctions = false;
        $watermark->withImage('non/existing/marker.png');
    }

    public function testThrowsExceptionIfDestinationNotWritable(): void
    {
        global $mockGlobalFunctions;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The specified destination non/existing is not writable!');

        $watermark = new Watermark('path/to/file.jpg');

        $mockGlobalFunctions = false;
        $watermark->withText('text')
            ->write('non/existing/output.jpg');
    }

    public function testThrowsExceptionIfSourceNotImageOrPDF(): void
    {
        global $mockGlobalFunctions;
        $mockGlobalFunctions = false;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage('The source file type text/x-php is not supported.');

        new Watermark(__FILE__);
    }

    public function testGetCommandReturnsStringCommand(): void
    {
        global $lastExecCommand;

        $watermark = new Watermark('path/to/file.jpg');
        $command = $watermark->withImage('path/to/logo.png')
            ->getCommand();

        $this->assertStringContainsString('composit', $command);
        $this->assertStringContainsString('path/to/logo.png', $command);
    }

    public function testSetPositionOnPositionList(): void
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setPosition = $watermark->setPosition(Watermark::POSITION_CENTER);
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setPosition);
        $this->assertContains(Watermark::POSITION_CENTER, $options);
    }

    public function testSetStyle(): void
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setStyle = $watermark->setStyle(Watermark::STYLE_IMG_COLORLESS);
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setStyle);
        $this->assertContains(Watermark::STYLE_IMG_COLORLESS, $options);
    }

    public function testSetTileSize(): void
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setStyle = $watermark->setTileSize(200, 150);
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setStyle);
        $this->assertContains([200, 150], $options);
    }

    public function testSetFont(): void
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setFont = $watermark->setFont('Arial');
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setFont);
        $this->assertContains('Arial', $options);
    }

    public function testSetFontSize(): void
    {
        $watermark = new Watermark('path/to/file.jpg');
        $setFontSize = $watermark->setFontSize(20);
        $options = $this->invokeProperty($watermark, 'options');

        $this->assertInstanceOf(Watermark::class, $setFontSize);
        $this->assertContains(20, $options);
    }

}
