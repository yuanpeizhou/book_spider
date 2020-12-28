<?php
// ini_set('memory_limit', '10M');
$filename = './IMG_7157.jpg';
$target = './1.jpg';


// $image = new Imagick($filename);
// $image->setResolution(300,300) ;
// $image->resampleImage(300,300,imagick::FILTER_UNDEFINED,1);
// $image->writeImage($target);

$im = new Imagick();
$im->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH);
$im->setImageResolution(300,300);
$im->readImage($filename);
$im->setImageFormat("jpg");
header("Content-Type: image/png");
echo $im;