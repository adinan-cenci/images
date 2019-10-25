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
$background = 'rgba(0,0,0,0.5)';

$text = new Text('Copyright Foo Bar.');
$text->fontFile('fonts/Roboto-Bold.ttf')
->padding(20)
->fontSize(20)
->lineHeight(20)
->align('left')
->color('rgba(255,255,255)')
->background($background);

$watermark = $text->getImage();

/*-----------------------------*/

$watermark->rotate(90, $background, 15);

$file->paste($watermark, 0, 0, $watermark->width, $watermark->height);

/*-----------------------------*/

header('Content-type: image/jpeg');
$file->imageJpg();
