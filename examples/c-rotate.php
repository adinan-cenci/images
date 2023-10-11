<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\File;


/*-----------------------------*/

$file = new File('images/original.jpeg');
$file->resize(400);
$file->rotate(45, 'rgba(255,255,255,0.5)');

/*-----------------------------*/

header("Content-type: image/png");
$file->imagepng();
