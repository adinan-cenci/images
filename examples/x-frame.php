<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\File;
use AdinanCenci\Images\TrueColor;


/*-----------------------------*/

$dog = new File('images/original.jpeg');

$dog->resize(300);
$dog->rotate(90);
$thumb = new TrueColor(500, 700);

$thumb->frame($dog);

/*-----------------------------*/

header('Content-type: image/png');
$thumb->imagePng();
