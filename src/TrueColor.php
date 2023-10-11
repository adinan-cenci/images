<?php
namespace AdinanCenci\Images;

class TrueColor extends Image 
{
    public function __construct(int $width, int $height, ?\GdImage $src = null) 
    {
        if (! $src) {
            $src = imagecreatetruecolor($width, $height);
        }

        $this->src      = $src;
        $this->width    = $width;
        $this->height   = $height;
        $this->ratio    = $width / $height;

        $this->alpha(true);
        $this->fill('rgba(0,0,0,2)');
    }
}
