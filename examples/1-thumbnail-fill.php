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

$image  = new File($file);
$thumb  = new TrueColor($width, $height);

$thumb->fillWith($image);

/*-----------------------------*/

header('Content-type: image/jpeg');
$thumb->imageJpg();
