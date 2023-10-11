<?php
namespace AdinanCenci\Images;

class Image
{
    protected int $width          = 0;

    protected int $height         = 0;

    /**
     * @var float
     */
    protected float $ratio        = 0;

    /**
     * @var \GdImage
     */
    protected \GdImage $src;

    protected bool $saveAlpha     = false;

    protected bool $alphaBlending = true;

    protected array $readOnly = ['src', 'width', 'height', 'ratio'];

    public function __construct(int $width, int $height, ?\GdImage $src = null)
    {
        if (! $src) {
            $src = imagecreate($width, $height);
        }

        $this->src      = $src;
        $this->width    = $width;
        $this->height   = $height;
        $this->ratio    = $width / $height;
    }

    public function __get(string $var)
    {
        if (in_array($var, $this->readOnly)) {
            return $this->{$var};
        }

        if (preg_match('/(.+)Height/', $var, $matches)) {
            return $this->parseHeightFraction($matches[1]);
        }

        $var = str_replace('Width', '', $var);

        return $this->parseWidthFraction($var);
    }

    /**
     * Returns a sample of the specified rectangle.
     * If no parameter is specified, a copy of the image is created.
     *
     * @param int $x
     * @param int $y
     * @param int|null $width
     * @param int|null $height
     *
     * @return AdinanCenci\Images\Image
     */
    public function copy(int $x = 0, int $y = 0, ?int $width = null, ?int $height = null) : Image
    {
        $width  = $width  ? $width  : $this->width;
        $height = $height ? $height : $this->height;

        $thumb  = self::newTrueColorTransparent($width, $height);

        imagecopyresampled($thumb, $this->src, 0, 0, $x, $y, $width, $height, $width, $height);

        return new Image($width, $height, $thumb);
    }

    /***********************************************
    *** Edditing
    ************************************************/

    /**
     * Resizes the image to the specified dimensions.
     *
     * If $height is not informed, the ratio will be preserved.
     * The same works if you inform the height but not $width.
     *
     * @param int|null $width
     * @param int|null $height
     *
     * @return self
     */
    public function resize(?int $width, ?int $height = null) : Image
    {
        if (!$width && !$height) {
            throw new \InvalidArgumentException('Inform the image\s new dimensions');
        }

        if ($width && !$height) {
            $height = (int) ($width / $this->ratio);
        } elseif (!$width && $height) {
            $width = (int) ($height * $this->ratio);
        }

        $newSrc = self::newTrueColorTransparent($width, $height);

        imagecopyresampled($newSrc, $this->src, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        $this->src              = $newSrc;
        $this->saveAlpha        = true;
        $this->alphaBlending    = false;

        $this->updateDimensions();
        return $this;
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     *
     * @return self
     */
    public function crop(int $x, int $y, int $width, int $height) : Image
    {
        $this->src = imagecrop($this->src, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
        $this->updateDimensions();
        return $this;
    }

    /**
     * @param AdinanCenci\Images\Image|\GdImage $image
     * @param int $x
     * @param int $y
     * @param int|null $width
     * @param int|null $height
     *
     * @return bool 
     */
    public function paste($image, int $x = 0, int $y = 0, ?int $width = null, ?int $height = null) : bool
    {
        if ($image instanceof Image) {
            $width      = $width  ? $width  : $image->width;
            $height     = $height ? $height : $image->height;
            $resource   = $image->src;
        } else if ($image instanceof \GdImage) {
            $resource   = $image;
            $width      = $width  ? $width  : imagesx($image);
            $height     = $height ? $height : imagesy($image);
        }

        $this->alpha(false);

        $success = imagecopyresampled($this->src, $resource, $x, $y, 0, 0, $width, $height, $image->width, $image->height);

        $this->alpha(true);

        return $success;
    }

    /**
     * @param int $angle
     * 
     * @return self
     */
    public function rotate(int $angle, $color = 'rgba(0,0,0,0)')
    {
        $color          = $this->allocateColor($color);
        $this->src      = imagerotate($this->src, $angle, $color);

        $this->saveAlpha();

        $this->updateDimensions();
        return $this;
    }

    /***********************************************
    *** Filters
    ************************************************/

    public function invert() 
    {
        imagefilter($this->src, IMG_FILTER_NEGATE);
    }

    public function greyScale() 
    {
        imagefilter($this->src, IMG_FILTER_GRAYSCALE);
    }

    /**
     * @param int $level Between -255 to 255
     */
    public function brightness($level) 
    {
        imagefilter($this->src, IMG_FILTER_BRIGHTNESS, $level);
    }

    /**
     * @param int $level
     * -100 = max contrast, 
     * 0    = no change
     * 100  = min contrast
     */
    public function contrast($level) 
    {
        imagefilter($this->src, IMG_FILTER_CONTRAST, $level);
    }

    /**
     * Adds or subtract rgb values from each pixel
     * From -255 to 255
     */
    public function colorize($r, $g, $b, $a = 0) 
    {
        imagefilter($this->src, IMG_FILTER_COLORIZE, $r, $g, $b, $a);
    }

    public function edge(int $times = 1) 
    {
        for ($a = 1; $a <= $times; $a++) {
            imagefilter($this->src, IMG_FILTER_EDGEDETECT);
        }
    }

    public function boss(int $times = 1) 
    {
        for ($a = 1; $a <= $times; $a++) {
            imagefilter($this->src, IMG_FILTER_EMBOSS);
        }
    }

    public function meanRemoval(int $times = 1) 
    {
        for ($a = 1; $a <= $times; $a++) {
            imagefilter($this->src, IMG_FILTER_MEAN_REMOVAL);
        }
    }

    public function gaussianBlur(int $times = 1) 
    {
        for ($a = 1; $a <= $times; $a++) {
            imagefilter($this->src, IMG_FILTER_GAUSSIAN_BLUR);
        }
    }

    public function blur(int $times = 1) 
    {
        for ($a = 1; $a <= $times; $a++) {
            imagefilter($this->src, IMG_FILTER_SELECTIVE_BLUR);
        }
    }

    public function smooth(int $level) 
    {
        imagefilter($this->src, IMG_FILTER_SMOOTH, $level);
    }

    /**
     * @param int $size Block size in pixels
     * @param bool $advanced Whether to use advanced pixelation effect or not
     */
    public function pixelate(int $size, bool $advanced = false) 
    {
        imagefilter($this->src, IMG_FILTER_PIXELATE, $size, $advanced);
    }

    /**
     * @param int $substraction Substraction level. This must not be higher or equal to the $addition level.
     * @param int $addition Effect addition level.
     * @param identifier/string $color
     */

    public function scatter($substraction, $addition, $color = []) 
    {
        if (! defined('IMG_FILTER_SCATTER')) {
            return false;
        }

        imagefilter($this->src, IMG_FILTER_SCATTER, $substraction, $addition, $color);
    }

    /***********************************************
    *** Helpful methods
    ************************************************/

    /** $paste $image at center of this one */
    public function centerIt($image)
    {
        $x = (int) (($this->width  - $image->width)  / 2);
        $y = (int) (($this->height - $image->height) / 2);
        return $this->paste($image, $x, $y, $image->width, $image->height);
    }

    /**
     * Will fill $this with $image
     */
    public function fillWith($image, $align = '')
    {
        $x      = 0;
        $y      = 0;

        if ($this->isThickerThan($image)) {
            // width wise
            $width  = $this->width;
            $height = $this->width / $image->ratio;

            // centers it vertically
            $y = (($height - $this->height) / 2) * -1;
        } else {
            $width  = $this->height * $image->ratio;
            $height = $this->height;

            // centers it horizontally
            $x = (($width - $this->width) / 2) * -1;
        }

        $x      = (int) $x;
        $y      = (int) $y;
        $width  = (int) $width;
        $height = (int) $height;

        $this->paste($image, $x, $y, $width, $height);
        return $this;
    }

    /**
     * Fit the entire $image neately inside $this
     *
     * @param Image $image
     *
     * @return self
     */
    public function fit(Image $image) : Image
    {
        $x      = 0;
        $y      = 0;

        // Centers inside of it without changing dimensions.
        if ($this->isLargerThan($image)) {

            $width  = $image->width;
            $height = $image->height;

            $x      = ($this->width - $image->width)   / 2;
            $y      = ($this->height - $image->height) / 2;

        // Centers vertically
        } else if ($this->isThinnerThan($image)) {

            $width  = $this->width;
            $height = $this->width / $image->ratio;

            // centers it vertically
            $y = ($this->height - $height) / 2;

        // Centers horizontaly
        } else {
            $width  = $this->height * $image->ratio;
            $height = $this->height;

            // centers it horizontally
            $x = ($this->width - $width) / 2;
        }

        $x      = (int) $x;
        $y      = (int) $y;
        $width  = (int) $width;
        $height = (int) $height;

        $this->paste($image, $x, $y, $width, $height);
        return $this;
    }

    /**
     * @param int $cx
     * @param int $cy
     * @param int $width
     * @param int $height
     *
     * @return bool
     */
    public function drawEllipse(int $cx, int $cy, int $width, int $height, $borderColor = [255, 255, 255])
    {
        $borderColor = $this->allocateColor($borderColor);
        return imageellipse($this->src, $cx, $cy, $width, $height, $borderColor);
    }

    /**
     * draws a filled retangle
     * @param int|array|string $color
     * @return bool
     */
    public function filledRectangle($color, $x = 0, $y = 0, $x2 = null, $y2 = null)
    {
        $x2 = $x2 ? $x2 : $this->width;
        $y2 = $y2 ? $y2 : $this->height;

        $color = $this->allocateColor($color);
        return imagefilledrectangle($this->src, $x, $y, $x2, $y2, $color);
    }

    /** @return bool */
    public function drawRetangle($color, $x, $y, $width, $height)
    {
        $x2 = $x + $width;
        $y2 = $y + $height;
        return $this->filledRectangle($color, $x, $y, $x2, $y2);
    }

    public function fill($color)
    {
        $color = $this->allocateColor($color);
        imagefill($this->src, 0, 0, $color);
    }

    // Draw a string horizontally
    public function string($font, $x, $y, $string, $color)
    {
        $color = $this->allocateColor($color);
        return imagestring($this->src, $font, $x, $y, $string, $color);
    }

    public function ttfText($fontSize, int $angle, int $x, int $y, $color, $fontFile, $text)
    {
        $color  = $this->allocateColor($color);
        return imagettftext($this->src, $fontSize, $angle, $x, $y, $color, $fontFile, $text);
    }

    /***********************************************
    *** Dimensions
    ************************************************/

    /**
     * @param AdinanCenci\Images\Image $image
     *
     * @return bool
     */
    public function isWiderThan(Image $image) : bool
    {
        return $this->width > $image->width;
    }

    /**
     * @param AdinanCenci\Images\Image $image
     *
     * @return bool
     */
    public function isTallerThan(Image $image) : bool
    {
        return $this->height > $image->height;
    }

    /**
     * @param AdinanCenci\Images\Image $image
     *
     * @return bool
     */
    public function isNarrowerThan(Image $image) : bool
    {
        return $this->width < $image->width;
    }

    /**
     * @param AdinanCenci\Images\Image $image
     *
     * @return bool
     */
    public function isThickerThan(Image $image) : bool
    {
        return $this->ratio > $image->ratio;
    }

    /**
     * @param AdinanCenci\Images\Image $image
     *
     * @return bool
     */
    public function isThinnerThan($image) : bool
    {
        return $this->ratio < $image->ratio;
    }

    /**
     * @param AdinanCenci\Images\Image $image
     *
     * @return bool
     */
    public function isLargerThan(Image $image) : bool
    {
        return $this->isWiderThan($image) && $this->isTallerThan($image);
    }

    protected function updateDimensions()
    {
        $this->width    = imagesx($this->src);
        $this->height   = imagesy($this->src);
        $this->ratio    = $this->width / $this->height;
    }

    /***********************************************
    *** Settings
    ************************************************/

    public function antialias(bool $bool = true)
    {
        return imageantialias($this->src, $bool);
    }

    public function alphaBlending($bool = true) 
    {
        imagealphablending($this->src, $bool);
        $this->alphaBlending = $bool;
    }

    public function saveAlpha($bool = true) 
    {
        imagesavealpha($this->src, $bool);
        $this->saveAlpha = $bool;
    }

    public function alpha($bool = true) 
    {
        $this->alphaBlending(!$bool);
        $this->saveAlpha($bool);
    }

    /***********************************************
    *** Text
    ************************************************/

    public static function imageTtfBbox(int $fontSize, int $angle, string $fontFile, string $text)
    {
        $box            = imagettfbbox($fontSize, $angle, $fontFile, $text);
        $box['width']   = $box[4] - $box[6];
        $box['height']  = $box[1] - $box[7];
        return $box;
    }

    /***********************************************
    *** Output image to browser or file
    ************************************************/

    public function imageJpg($filename = null, $quality = 100)
    {
        return self::image('imagejpeg', $this->src, $filename, $quality);
    }

    public function imageGif($filename = null, $quality = 100)
    {
        return self::image('imagegif', $this->src, $filename, $quality);
    }

    public function imagePng($filename = null, $quality = 100)
    {
        return self::image('imagepng', $this->src, $filename, $quality);
    }

    public function imageWebp($filename = null, $quality = 100)
    {
        return self::image('imagewebp', $this->src, $filename, $quality);
    }

    public function imageWbmp($filename = null, $color = null) 
    {
        if ($color) {
            $color = $this->allocateColor($color);
        }

        return imagewbmp($this->src, $filename, $color);
    }

    /***********************************************
    *** Size
    ************************************************/

    public function getWidthPerc($perc) 
    {
        return ($this->width / 100) * $perc;
    }

    public function getHeightPerc($perc) 
    {
        return ($this->width / 100) * $perc;   
    }

    public function getPerc($perc) 
    {
        return $this->getWidthPerc($perc);
    }

    //--------------

    public function getWidthFraction($numerator, $denominator) 
    {
        return Helper::getFraction($this->width, $numerator, $denominator);
    }

    public function getHeightFraction($numerator, $denominator) 
    {
        return Helper::getFraction($this->height, $numerator, $denominator);
    }

    //--------------

    protected function parseWidthFraction($str) 
    {
        if (! $fr = Helper::parseFraction($str)) {
            return null;
        }

        return $this->getWidthFraction($fr['numerator'], $fr['denominator']);
    }

    protected function parseHeightFraction($str) 
    {
        if (! $fr = Helper::parseFraction($str)) {
            return null;
        }

        return $this->getHeightFraction($fr['numerator'], $fr['denominator']);
    }

    //--------------

    /** makes sure that $color is a color identifier */
    public function allocateColor($color)
    {
        $r = $g = $b = $a = 0;

        $rgba = Helper::colorToAlocate($color);

        list($r, $g, $b, $a) = $rgba;

        return $a === false ?
            imagecolorallocate($this->src, $r, $g, $b) : 
            imagecolorallocatealpha($this->src, $r, $g, $b, $a);
    }

    public static function newTrueColorTransparent(int $width, int $height) 
    {
        $src = imagecreatetruecolor($width, $height);
        imagesavealpha($src, true);
        imagealphablending($src, false);
        $transparent = imagecolorallocatealpha($src, 0, 0, 0, 127);
        imagefill($src, 0, 0, $transparent);
        return $src;
    }

    public static function createFromString($data) 
    {
        //$data = base64_decode($data);
        $src  = imagecreatefromstring($data);
        $w    = imagesx($src);
        $h    = imagesy($src);

        return new self($w, $h, $src);
    }

    protected static function createFromType($file, $type)
    {
        $functions = array(
            'image/gif'             => 'imagecreatefromgif',
            'image/png'             => 'imagecreatefrompng',
            'image/jpeg'            => 'imagecreatefromjpeg',
            'image/jpg'             => 'imagecreatefromjpeg', 
            'image/webp'            => 'imagecreatefromwebp', 
            'image/vnd.wap.wbmp'    => 'imagecreatefromwbmp'
        );

        $func   = $functions[$type];
        $thumb  = @$func($file);

        if ($type == 'image/png') {            
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }

        return $thumb;
    }

    protected static function image($func, $src, $filename = null, $quality = 100)
    {
        if ($func == 'imagepng') {
            $quality = round($quality / 14.28);
        }

        return $func($src, $filename, $quality);
    }
}
