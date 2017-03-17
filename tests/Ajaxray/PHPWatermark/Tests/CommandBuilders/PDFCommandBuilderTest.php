<?php
/**
 * Created by PhpStorm.
 * User: Anis Ahmad <anis.programmer@gmail.com>
 * Date: 3/9/17
 * Time: 3:17 PM
 */

namespace Ajaxray\PHPWatermark\Tests\CommandBuilders;


use Ajaxray\PHPWatermark\CommandBuilders\PDFCommandBuilder;
use Ajaxray\PHPWatermark\Watermark;

class PDFCommandBuilderTest extends \PHPUnit_Framework_TestCase
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

    private $cmdText = 'convert \'path/name.pdf\' -gravity Center -quality 100 -density 100 -pointsize 24 -font \'Arial\' -fill "rgba(255,255,255,0.3)" -annotate +0+0 \'LIFE CHANGING BOOK\' -fill "rgba(0,0,0,0.3)" -annotate +1+1 \'LIFE CHANGING BOOK\'  \'path/output.pdf\'';
    private $cmdImg = "convert 'path/logo.png' -alpha set -channel A -evaluate set 30%  miff:- | convert -density 100 'path/name.pdf' null: - -gravity Center -geometry +0+0 -quality 100 -compose multiply -layers composite 'path/output.pdf'";

    /**
     * @var PDFCommandBuilder
     */
    private $builder;

    /**
     * PDFCommandBuilderTest constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->builder = new PDFCommandBuilder('path/name.pdf');
    }

    public function testTextWatermarking()
    {
        $execCommand = $this->getTxtCommandWithOption([]);
        $this->assertEquals($this->cmdText, $execCommand);
    }

    public function testTextWatermarkingWithRotate()
    {
        $execCommand = $this->getTxtCommandWithOption(['rotate' => 30]);
        $this->assertEquals(str_replace('-annotate ', '-annotate 30x30', $this->cmdText), $execCommand);
    }

    public function testTextWatermarkingConfigureOpacity()
    {
        $execCommand = $this->getTxtCommandWithOption(['opacity' => .6]);
        $this->assertEquals(str_replace(',0.3)', ',0.6)', $this->cmdText), $execCommand);
    }

    public function testTextWatermarkingConfigureFont()
    {
        $execCommand = $this->getTxtCommandWithOption(['fontSize' => 48, 'font' => 'monospace']);
        $this->assertEquals(str_replace(['24', 'Arial'], ['48', 'monospace'], $this->cmdText), $execCommand);
    }

    public function testTextWatermarkingConfigurePosition()
    {
        $execCommand = $this->getTxtCommandWithOption([
            'position' => Watermark::POSITION_BOTTOM_RIGHT,
            'offsetX' => '220',
            'offsetY' => '50',
        ]);
        $this->assertEquals(str_replace(['Center', '+0+0', '+1+1'], ['SouthEast', '+220+50', '+221+51'], $this->cmdText), $execCommand);
    }

    public function testImageWatermarkingBasic()
    {
        $execCommand = $this->builder->getImageMarkCommand('path/logo.png', 'path/output.pdf', $this->options);
        $this->assertEquals($this->cmdImg, $execCommand);
    }

    public function testImageWatermarkingWithLocationChange()
    {
        $execCommand = $this->getImgCommandWithOption([
            'position' => Watermark::POSITION_BOTTOM_RIGHT,
            'offsetX'  => 50,
            'offsetY'  => 100,
        ]);
        $expected = str_replace('-gravity Center -geometry +0+0', '-gravity SouthEast -geometry +50+100', $this->cmdImg);
        $this->assertEquals($expected, $execCommand);
    }

    public function testImageWatermarkingWithOpacityChange()
    {
        $execCommand = $this->getImgCommandWithOption(['opacity' => .7]);
        $expected = str_replace('-evaluate set 30%', '-evaluate set 70%', $this->cmdImg);

        $this->assertEquals($expected, $execCommand);
    }

    private function getTxtCommandWithOption(array $options)
    {
        $options = array_merge($this->options, $options);
        return $this->builder->getTextMarkCommand('LIFE CHANGING BOOK', 'path/output.pdf', $options);
    }

    private function getImgCommandWithOption(array $options)
    {
        $options = array_merge($this->options, $options);
        return $this->builder->getImageMarkCommand('path/logo.png', 'path/output.pdf', $options);
    }

}
