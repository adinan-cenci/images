<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\File;
use AdinanCenci\Images\TrueColor;

/*-----------------------------*/

$image = new File('images/original.jpeg');
$image->resize(500);
$image->scatter(3, 5);

/*-----------------------------*/

header("Content-type: image/png");
$image->imagepng();
