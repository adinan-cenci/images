<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\Image;
use AdinanCenci\Images\File;

/*-----------------------------*/

$png = new File('images/transparent-png.png');

/*-----------------------------*/

header("Content-type: image/png");
$png->imagepng();
