<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../src/Image.php';
require '../src/File.php';

use AdinanCenci\Images\Image;
use AdinanCenci\Images\File;

/*-----------------------------*/

$file = new File('images/dog.jpeg');

$file->resize(500);

header("Content-type: image/jpeg");
$file->imagejpg();
