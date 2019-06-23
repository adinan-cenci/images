<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\Image;
use AdinanCenci\Images\File;

/*-----------------------------*/

$file = new File('images/dog.jpeg');
$file->crop(255, 155, 1125, 1086);

header("Content-type: image/jpeg");
$file->imagejpg();
