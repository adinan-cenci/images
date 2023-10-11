<?php 
namespace AdinanCenci\Images\Tests;

use PHPUnit\Framework\TestCase;
use AdinanCenci\Images\Helper;

class ColorTest extends TestCase
{
    public function testReadRgb() 
    {
        $rgb = Helper::readRgbColor('rgb(255,100,50)');
        $this->assertEquals([255, 100, 50, null], $rgb);

        $rgb = Helper::readRgbColor('20,90,160');
        $this->assertEquals([20, 90, 160, null], $rgb);
    }

    public function testReadRgba() 
    {
        $rgba = Helper::readRgbColor('rgb(255,100,50,0.5)');
        $this->assertEquals([255, 100, 50, 0.5], $rgba);

        $rgba = Helper::readRgbColor('20,90,160,0.3');
        $this->assertEquals([20, 90, 160, 0.3], $rgba);
    }

    public function testReadHexadecimal() 
    {
        $rgb = Helper::readHexadecimalColor('#000');
        $this->assertEquals([0, 0, 0, null], $rgb);

        $rgb = Helper::readHexadecimalColor('#ff0000');
        $this->assertEquals([255, 0, 0, null], $rgb);
    }

    public function testReadHexadecimalWithAlpha() 
    {
        $rgba = Helper::readHexadecimalColor('#ffffffff');
        $this->assertEquals([255, 255, 255, 1], $rgba);
    }

    public function testColorToAlocate() 
    {
        $rgba = Helper::colorToAlocate('#ffffffff');
        $this->assertEquals([255, 255, 255, 0], $rgba);

        $rgba = Helper::colorToAlocate('#ffffff00');
        $this->assertEquals([255, 255, 255, 127], $rgba);

        $rgba = Helper::colorToAlocate('rgba(255,255,255,1)');
        $this->assertEquals([255, 255, 255, 0], $rgba);

        $rgba = Helper::colorToAlocate('rgba(255,255,255,0)');
        $this->assertEquals([255, 255, 255, 127], $rgba);
    }
}
