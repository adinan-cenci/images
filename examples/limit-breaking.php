<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '16M');

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\Repeater;
use AdinanCenci\Images\File;

/*-----------------------------*/

$f = new File('./giant.jpg');
$f->resize(200);

/*-----------------------------*/

header('Content-type: image/png');
$f->imagePng();
