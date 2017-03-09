<?php
/**
 * Created by PhpStorm.
 * User: ajaxray
 * Date: 3/9/17
 * Time: 3:17 PM
 */

namespace Ajaxray\PHPWatermark\CommandBuilders;


class ImageCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $command = 'convert \'examples/img/mountain.jpg\' -pointsize 36 -font \'Arial\'  -draw "rotate 30 gravity Center fill "rgba\(255,255,255,0.4\)" text 0,0 \'CONFIDENTIAL\' fill "rgba\(0,0,0,0.4\)" text 1,1 \'CONFIDENTIAL\'"  \'output.jpg\'';

    public function test()
    {
//        global $lastExecCommand;
//        $watermark = new Watermark('path/to/file.png');
//        $watermark->setFontSize(36);
//        $watermark->setRotate(30);
//        $watermark->withText('CONFIDENTIAL', 'output.jpg');
//        // die($lastExecCommand);
//
//        $this->assertEquals('expected command -and -args', $lastExecCommand);
    }

    /**
     * - String params are escaped
     * - Options are changing
     * - Check command with all defaults
     * - Check command with all defaults with tiles
     * - Check command with all custom
     * - Check command with all custom with tiles
     * - Check individually on/off options like rotate
     */

}
