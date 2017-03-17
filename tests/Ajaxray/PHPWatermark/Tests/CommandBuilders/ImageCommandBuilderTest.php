<?php
/**
 * Created by PhpStorm.
 * User: Anis Ahmad <anis.programmer@gmail.com>
 * Date: 3/9/17
 * Time: 3:17 PM
 */

namespace Ajaxray\PHPWatermark\Tests\CommandBuilders;


use Ajaxray\PHPWatermark\CommandBuilders\ImageCommandBuilder;
use Ajaxray\PHPWatermark\Watermark;

class ImageCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $options = [
        'position' => 'Center',
        'offsetX' => 0,
        'offsetY' => 0,
        'tiled' => false,
        'tileSize' => [100, 100],
        'font' => 'Arial',
        'fontSize' => 24,
        'opacity' => 0.3,
        'rotate' => 0,
        'style' => 1, // IMG_STYLE_DISSOLVE or TEXT_STYLE_BEVEL
    ];

    private $cmdText = 'convert \'path/source.jpg\' -pointsize 24 -font \'Arial\'  -draw " gravity Center fill "rgba\(255,255,255,0.3\)" text 0,0 \'ajaxray.com\' fill "rgba\(0,0,0,0.3\)" text 1,1 \'ajaxray.com\'"  \'path/output.jpg\'';
    private $cmdTiledText = 'convert -size 100x100 xc:none  -pointsize 24 -font \'Arial\' -gravity Center  -draw " gravity Center fill "rgba\(255,255,255,0.3\)" text 0,0 \'ajaxray.com\' fill "rgba\(0,0,0,0.3\)" text 1,1 \'ajaxray.com\'"  miff:-  | composite -tile - \'path/source.jpg\'  \'path/output.jpg\'';
    private $cmdImg = "composite -gravity Center -geometry +0+0 -dissolve 30%  'path/logo.png' 'path/source.jpg' 'path/output.jpg'";

    /**
     * @var ImageCommandBuilder
     */
    private $builder;

    /**
     * ImageCommandBuilderTest constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->builder = new ImageCommandBuilder('path/source.jpg');
    }

    public function testBasicWatermarkingWithText()
    {
        $execCommand = $this->getTxtCommandWithOption([]);
        $this->assertEquals($this->cmdText, $execCommand);
    }

    public function testWatermarkingWithChangingTextLocation()
    {
        $execCommand = $this->getTxtCommandWithOption([
            'position' => Watermark::POSITION_BOTTOM_LEFT,
            'offsetX'  => 10,
            'offsetY'  => 15,
        ]);
        $expected = str_replace(['gravity Center', 'text 0,0', 'text 1,1'], ['gravity SouthWest', 'text 10,15', 'text 11,16'], $this->cmdText);
        $this->assertEquals($expected, $execCommand);
    }

    public function testWatermarkingWithChangingTextOpacity()
    {
        $execCommand = $this->getTxtCommandWithOption(['opacity' => .7]);
        $expected = str_replace(['255,255,255,0.3', '0,0,0,0.3'], ['255,255,255,0.7', '0,0,0,0.7'], $this->cmdText);
        $this->assertEquals($expected, $execCommand);
    }

    public function testWatermarkingWithChangingTextRotation()
    {
        $execCommand = $this->getTxtCommandWithOption(['rotate' => 15]);
        $expected = str_replace('-draw "', '-draw "rotate 15', $this->cmdText);
        $this->assertEquals($expected, $execCommand);
    }

    public function testWatermarkingWithChangingTextFont()
    {
        $execCommand = $this->getTxtCommandWithOption(['font' => 'sans-serif', 'fontSize' => 36]);
        $expected = str_replace('-pointsize 24 -font \'Arial\'', '-pointsize 36 -font \'sans-serif\'', $this->cmdText);
        $this->assertEquals($expected, $execCommand);
    }

    public function testWatermarkingWithTiledText()
    {
        $execCommand = $this->getTxtCommandWithOption(['tiled' => true]);
        $this->assertEquals($this->cmdTiledText, $execCommand);
    }

    public function testBasicWatermarkingWithImage()
    {
        $execCommand = $this->getImgCommandWithOption([]);
        $this->assertEquals($this->cmdImg, $execCommand);
    }

    public function testWatermarkingWithChangingImageLocation()
    {
        $execCommand = $this->getImgCommandWithOption([
            'position' => Watermark::POSITION_TOP_RIGHT,
            'offsetX'  => 100,
            'offsetY'  => 150,
        ]);
        $expected = str_replace(['gravity Center', '+0+0'], ['gravity NorthEast', '+100+150'], $this->cmdImg);
        $this->assertEquals($expected, $execCommand);
    }

    public function testWatermarkingWithChangingImageOpacity()
    {
        $execCommand = $this->getImgCommandWithOption(['opacity' => .5]);
        $expected = str_replace('30%', '50%', $this->cmdImg);
        $this->assertEquals($expected, $execCommand);
    }

    public function testWatermarkingWithChangingImageStyle()
    {
        $execCommand = $this->getImgCommandWithOption(['style' => Watermark::STYLE_IMG_COLORLESS]);
        $expected = str_replace('-dissolve', '-watermark', $this->cmdImg);
        $this->assertEquals($expected, $execCommand);
    }

    private function getTxtCommandWithOption(array $options)
    {
        $options = array_merge($this->options, $options);
        return $this->builder->getTextMarkCommand('ajaxray.com', 'path/output.jpg', $options);
    }

    private function getImgCommandWithOption(array $options)
    {
        $options = array_merge($this->options, $options);
        return $this->builder->getImageMarkCommand('path/logo.png', 'path/output.jpg', $options);
    }

}
