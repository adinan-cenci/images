<?php
namespace AdinanCenci\Images;

class TrueColor extends Image 
{
    public function __construct(int $width, int $height) 
    {
        $this->src      = imagecreatetruecolor($width, $height);
        $this->width    = $width;
        $this->height   = $height;
        $this->ratio    = $width / $height;

        $this->alpha(true);
        $this->colorFill('rgba(0,0,0,0)');
    }
}
