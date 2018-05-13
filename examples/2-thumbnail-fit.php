<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../src/Image.php';
require '../src/TrueColor.php';
require '../src/File.php';

use AdinanCenci\Images\Image;
use AdinanCenci\Images\TrueColor;
use AdinanCenci\Images\File;

/*-----------------------------*/

$file       = 'images/a.jpg';
$width      = 200;
$height     = 400;

/*-----------------------------*/

$image = new File($file);
$thumb = new TrueColor($width, $height);
$thumb->drawRetangle([255, 255, 255], 0, 0, $width, $height);

$thumb->fit($image);

/*-----------------------------*/

header('Content-type: '.$image->mime);

switch ($image->mime) {
    case 'image/gif':
        $thumb->imagegif();
        break;
    case 'image/png':
        $thumb->imagepng();
        break;
    case 'image/jpeg':
    case 'image/jpg':
        $thumb->imagejpg();
        break;
}
