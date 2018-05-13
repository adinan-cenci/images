<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../src/Image.php';
require '../src/TrueColor.php';
require '../src/File.php';
require '../src/Text.php';

use AdinanCenci\Images\Image;
use AdinanCenci\Images\TrueColor;
use AdinanCenci\Images\File;
use AdinanCenci\Images\Text;

/*-----------------------------*/

$fontFile = 'fonts/Roboto-Bold.ttf';
$text = 
'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';


$t = new Text($text);
$t->fontFile($fontFile)
->alignment('justify')
->background('#fff')
->color('#0000ff')
->padding(20)
->width(500);


$imagem = $t->getImage();

header('Content-type: image/png');
$imagem->imagePng();