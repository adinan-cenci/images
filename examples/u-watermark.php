<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\Image;
use AdinanCenci\Images\TrueColor;
use AdinanCenci\Images\File;
use AdinanCenci\Images\Text;

/*-----------------------------*/

$file = new File('images/original.jpeg');
$file->resize(500);

/*-----------------------------*/

$text = new Text('Lorem ipsum dolor sit amet,
consectetur adipisicing elit.');

$text->fontFile('fonts/Roboto-Bold.ttf')
->alignment('center')
->background('rgba(0,0,0,0.3)')
->color('rgba(255,255,255)')
->padding(20)
->fontSize(20)
->lineHeight(20);

$label = $text->getImage();

/*-----------------------------*/

$file->paste($label);

/*-----------------------------*/

header('Content-type: image/jpeg');
$file->imageJpg();
