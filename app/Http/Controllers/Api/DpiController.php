<?php

namespace App\Http\Controllers\Api;

// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\DB;
/**
 * 24FA
 */
class DpiController extends CommonController{

    public function dpi(){
        $filename = './IMG_7157.jpg';
        // $target = './1.jpg';
        $im = new \Imagick();
        $im->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH);
        $im->setImageResolution(300,300);
        $im->readImage($filename);
        $im->setImageFormat("jpg");
        header("Content-Type: image/png");
        echo $im;
    }

}