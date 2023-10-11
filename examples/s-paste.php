<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\File;


/*-----------------------------*/

$dog = new File('images/original.jpeg');
$png = new File('images/transparent-png.png');

$dog->resize(500);
$png->resize(300);

$dog->paste($png);

/*-----------------------------*/

header('Content-type: image/png');
$dog->imagePng();
