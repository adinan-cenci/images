<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../src/Image.php';
require '../src/File.php';

use AdinanCenci\Images\Image;
use AdinanCenci\Images\File;

/*-----------------------------*/

$a = new File('images/a.jpg');
$a->resize(100);

header("Content-type: image/jpeg");
$a->imagejpg();