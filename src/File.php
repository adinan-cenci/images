<?php
namespace AdinanCenci\Images;

class File extends Image
{
    protected $file = '';

    public function __construct($file)
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
