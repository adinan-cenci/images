<?php
namespace AdinanCenci\Images;

class TrueColor extends Image 
{
    public function __construct($width, $height, $src = null) 
    {
        if (! $src) {
            $src = imagecreatetruecolor($width, $height);
        }

        $this->src      = $src;
        $this->width    = $width;
        $this->height   = $height;
        $this->ratio    = $width / $height;

        $this->alpha();
        $this->fill('rgba(0,0,0,2)');
    }
}
