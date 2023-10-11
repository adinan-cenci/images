<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\File;


/*-----------------------------*/

$file = new File('images/original.jpeg');
$file->crop(900, 700, 330, 200);

/*-----------------------------*/

header("Content-type: image/png");
$file->imagePng();
