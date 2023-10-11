<?php
namespace AdinanCenci\Images;

class File extends Image
{
    protected string $file = '';

    protected array $readOnly = ['src', 'width', 'height', 'mime', 'ratio', 'file'];

    public function __construct(string $file)
    {
        $info           = getimagesize($file);

        $this->file     = $file;
        $this->width    = $info[0];
        $this->height   = $info[1];
        $this->mime     = $info['mime'];
        $this->ratio    = $this->width / $this->height;
        $this->src      = self::createFromType($file, $this->mime);

        if ($this->isJpg) {
            $this->fixOrientation();
        }
    }

    public function __get(string $var) 
    {
        if ($var == 'isPng') {
            return $this->mime == 'image/png';
        }

        if ($var == 'isJpg') {
            return in_array($this->mime, array('image/jpg', 'image/jpeg'));
        }

        if ($var == 'isGif') {
            return $this->mime == 'image/gif';
        }

        return parent::__get($var);
    }

    /**
     * Some images may have metadata indicating the orientation of the camera 
     * was in when the picture was taken. This help different devices to display 
     * the image properly. The gd library will not carry this metadata forwards 
     * so we need to fix the image by rotating it.
     */
    protected function fixOrientation()
    {
        $exif = exif_read_data($this->file);

        if (empty($exif['Orientation'])) {
            return false;
        }

        switch ($exif['Orientation']) {
            case 3:
                $angle = 180;
                break;
            case 6:
                $angle = -90;
                break;
            case 8:
                $angle = 90;
                break;
        }

        if (! isset($angle)) {
            return false;
        }

        $this->rotate($angle);

        return true;
    }
}
