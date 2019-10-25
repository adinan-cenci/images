<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\File;
use AdinanCenci\Images\TrueColor;

/*-----------------------------*/

$image = new File('images/original.jpeg');
$thumb = new TrueColor(200, 300);
$thumb->fillWith($image);

/*-----------------------------*/

header("Content-type: image/png");
$thumb->imagepng();
