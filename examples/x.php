<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*-----------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\Images\Repeater;
use AdinanCenci\Images\File;

/*-----------------------------*/

$f = new File('./images/original.jpeg');
$f->resize(200);

$r = new Repeater($f);
$r->columns(4)->rows(3);
$r->modify(function($c, $r) 
{

	if (($c + 1) % 2 == 0) {
		return array(
			'x' => 0, 
			'y' => 60
		);
	}

	return array('x' => 0, 'y' => 0);
});

$r->order();
$t = $r->generateImage();

/*-----------------------------*/

header('Content-type: image/png');
$t->imagePng();
