<?php
namespace AdinanCenci\Images;

class Image
{
    protected $width    = 0;
    protected $height   = 0;

    /** @var float $ratio Quotient between the $width and $height */
    protected $ratio    = 0;

    protected $mime     = '';

    /** @var image resource identifier $src */
    protected $src      = null;

    public function __construct($width, $height, $src = null)
    {
        if (! $src) {
            $src = self::create($width, $height);
        }

        $this->src      = $src;
        $this->width    = $width;
        $this->height   = $height;
        $this->ratio    = $width / $height;
    }

    public function __get($var)
    {
        $readOnly = array('src', 'width', 'height', 'mime', 'ratio', 'file');
        if (in_array($var, $readOnly)) {
            return $this->{$var};
        }

        if ($var == 'half') {
            return $this->oneOfTwo;
        }

        if ($var == 'isPng') {
            return $this->mime == 'image/png';
        }

        if ($var == 'isJpg') {
            return in_array($this->mime, array('image/jpg', 'image/jpeg'));
        }

        if (preg_match('/([a-z]+)Of([A-Z][a-z]+)/', $var, $matches)) {
            $fraction   = self::strToInt($matches[1]);
            $divisor    = self::strToInt($matches[2]);

            $value      = $this->width;

            return ($value / $divisor) * $fraction;
        }
    }

    public function antialias(bool $enabled = true)
    {
        return imageantialias($this->src, $enabled);
    }

    /** @return Image */
    public function copy($x, $y, $width, $height)
    {
        $thumb = self::create($width, $height);

        imagecopyresampled($thumb, $this->src, 0, 0, $x, $y, $width, $height, $width, $height);

        return new Image($width, $height, $thumb);
    }

    public function crop($x, $y, $width, $height)
    {
        $this->src = imagecrop($this->src, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
        $this->updateDimensions();
        return $this;
    }

    /** @return bool */
    public function paste($image, $x = 0, $y = 0, $width = null, $height = null)
    {
        $width  = $width ? $width : $image->width;
        $height = $height ? $height : $image->height;

        return imagecopyresampled($this->src, $image->src, $x, $y, 0, 0, $width, $height, $image->width, $image->height);
    }

    public function centerIt($image)
    {
        $x = (($this->width - $image->width) / 2);
        $y = (($this->height - $image->height) / 2);
        return $this->paste($image, $x, $y, $image->width, $image->height);
    }

    public function rotate($angle, $color = array(255, 255, 255, 0))
    {
        $color          = $this->allocate($color);
        $this->src      = imagerotate($this->src, $angle, $color);

        if ($this->isPng) {
          self::alpha($this->src);
        }

        $this->updateDimensions();
        return $this;
    }

    public function resize($width, $height = null)
    {
        if ($height == null) {
            $height = $width / $this->ratio;
        }

        $newSrc = self::trueColor($width, $height);

        if ($this->isPng) {
            self::alpha($newSrc);
        }

        imagecopyresampled($newSrc, $this->src, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        $this->src = $newSrc;

        $this->updateDimensions();
        return $this;
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

        $this->paste($image, $x, $y, $width, $height);
        return $this;
    }

    /**
     * Fit the entire $image neately inside $this
     * @param Image $image
     * @return $this
     */
    public function fit($image, $align = '')
    {
        $x      = 0;
        $y      = 0;

        if ($this->isThinerThan($image)) {
            $width  = $this->width;
            $height = $this->width / $image->ratio;

            // centers it vertically
            $y = ($this->height - $height) / 2;
        } else {
            $width  = $this->height * $image->ratio;
            $height = $this->height;

            // centers it horizontally
            $x = ($this->width - $width) / 2;
        }

        $this->paste($image, $x, $y, $width, $height);
        return $this;
    }

    public function drawEllipse($cx, $cy, $width, $height, $borderColor = array(255, 255, 255))
    {
        $borderColor = $this->allocate($borderColor);
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

        $color = $this->allocate($color);
        return imagefilledrectangle($this->src, $x, $y, $x2, $y2, $color);
    }

    /** @return bool */
    public function drawRetangle($color, $x, $y, $width, $height)
    {
        $x2 = $x + $width;
        $y2 = $y + $height;
        return $this->filledRectangle($color, $x, $y, $x2, $y2);
    }

    public function paint($color)
    {
        return $this->drawRetangle($color, 0, 0, $this->width, $this->height);
    }

    // Draw a string horizontally
    public function string($font, $x, $y, $string, $color)
    {
        $color = $this->allocate($color);
        return imagestring($this->src, $font, $x, $y, $string, $color);
    }

    public function ttfText($fontSize, $angle, $x, $y, $color, $fontFile, $text)
    {
        $color  = $this->allocate($color);
        return imagettftext($this->src, $fontSize, $angle, $x, $y, $color, $fontFile, $text);
    }

    /** @return bool */
    public function isWiderThan($image)
    {
        return $this->width > $image->width;
    }

    /** @return bool */
    public function isNarrowerThan($image) {
        return $this->width < $image->width;
    }

    /** @return bool */
    public function isThickerThan($image)
    {
        return $this->ratio > $image->ratio;
    }

    /** @return bool */
    public function isThinerThan($image)
    {
        return $this->ratio < $image->ratio;
    }

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

    protected function updateDimensions()
    {
        $this->width    = imagesx($this->src);
        $this->height   = imagesy($this->src);
        $this->ratio    = $this->width / $this->height;
    }

    public static function imageTtfBbox($fontSize, $angle = 0, $fontFile, $text)
    {
        $box            = imagettfbbox($fontSize, $angle, $fontFile, $text);
        $box['width']   = $box[4] - $box[6];
        $box['height']  = $box[1] - $box[7];
        return $box;
    }

    public static function rgbaRegex($string)
    {
        $match = preg_match('/([0-9]+),([0-9]+),([0-9]+)(,([0-9.]+))?/', $string, $matches);

        if (! $match) {
            return null;
        }

        $rgba = [
            $matches[1],
            $matches[2],
            $matches[3],
        ];

        if (isset($matches[5])) {
            $rgba[] = $matches[5];
        }

        return $rgba;
    }

    public static function hexadecimalToRgb($hexadecimal)
    {
        $hexadecimal = trim($hexadecimal, '#');

        if (strlen($hexadecimal) == 3) {
            $r      = hexdec(substr($hexadecimal, 0, 2));
            return array_fill(0, 3, $r);
        }

        $hex    = str_split($hexadecimal, 2);
        $rgba   = array_map('hexdec', $hex);

        if (isset($rgba[3])) {
            $perc = ($rgba[3] / 255) * 100;
            $rgba[3] = 0.01 * $perc;
        }

        return $rgba;
    }

    protected static function createFromType($file, $type)
    {
        $functions = array(
            'image/gif'     => 'imagecreatefromgif',
            'image/png'     => 'imagecreatefrompng',
            'image/jpeg'    => 'imagecreatefromjpeg',
            'image/jpg'     => 'imagecreatefromjpeg'
        );

        $func   = $functions[$type];
        $thumb  = $func($file);

        if ($type == 'image/png') {
            self::alpha($thumb);
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

    protected static function create($width, $height)
    {
        $thumb = imagecreate($width, $height);
        return $thumb;
    }

    protected static function trueColor($width, $height)
    {
        $resource = imagecreatetruecolor($width, $height);

        return $resource;
    }

    public function saveAlpha($bool = true)
    {
        self::alpha($this->src, $bool);
    }

    /** makes sure that $color is a color identifier */
    public function allocate($color)
    {
        $r = $g = $b = $a = 0;

        if (is_string($color) and substr_count($color, '#')) {
            $color = self::hexadecimalToRgb($color);
        } else if (is_string($color)) {
            $color = self::rgbaRegex($color);
        }

        if (isset($color[3]) and $color[3] == 0) {
            $color[3]   = 127;
        } else if (isset($color[3])) {
            $perc       = $color[3] / 1 * 100;
            $alpha      = ceil(127 - (127 / 100 * $perc));
            $color[3]   = $alpha;
        }

        $args = count(func_get_args()) > 1 ? func_get_args() : $color;
        list($r, $g, $b) = $args;
        $a = isset($args[3]) ? $args[3] : $a;

        if ($a == 0) {
            return imagecolorallocate($this->src, $r, $g, $b);
        }

        if ($a > 0) {
            return imagecolorallocatealpha($this->src, $r, $g, $b, $a);
        }

        return $color;
    }

    public static function alpha($src, $bool = true)
    {
        imagesavealpha($src, $bool);
        imagealphablending($src, !$bool);
    }

    public static function pointToPixel($points)
    {
        return $points * 1.333333;
    }

    protected static function strToInt($str)
    {
        $str    = strtolower($str);
        $nbrs   = array(
            'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'height' => 8, 'nine' => 9, 'ten' => 10, 'eleven' => 11, 'twelve' => 12,
            'half' => 2, 'third' => 3, 'fourth' => 4, 'fifth' => 5, 'sixth' => 6, 'seventh' => 7, 'eight' => 8, 'nineth' => 9, 'tenth' => 10, 'eleventh' => 11, 'twelfth'
        );

        return isset($nbrs[$str]) ? $nbrs[$str] : false;
    }
}
