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

$file = new File('images/dog.jpeg');
$file->crop(255, 155, 1125, 1086);
$file->resize(500);

/*-----------------------------*/

$text = 
'Lorem ipsum dolor sit amet, 
consectetur adipisicing elit.';

$text = new Text($text);

$text->fontFile('fonts/Roboto-Bold.ttf')
->alignment('center')
->background('rgba(0,0,0,0.3)')
->color('#fff')
->padding(20)
->fontSize(20)
->lineHeight(20);

$label = $text->getImage();

/*-----------------------------*/

$file->paste($label, ($file->width - $label->width) / 2, $file->height - $label->height);

/*-----------------------------*/

header('Content-type: image/jpeg');
$file->imageJpg();