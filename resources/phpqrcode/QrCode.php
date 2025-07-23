<?php
namespace resources\phpqrcode;

class QrCode{
    public static function png(string $data, ?string $file = null, int $size = 3, int $margin = 4): void{
        $imgSize = 100;
        $im = imagecreatetruecolor($imgSize, $imgSize);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $imgSize - 1, $imgSize - 1, $white);
        imagerectangle($im, 0, 0, $imgSize - 1, $imgSize - 1, $black);
        imagestring($im, 2, 5, ($imgSize/2)-7, 'QR', $black);
        if($file){
            imagepng($im, $file);
        }else{
            imagepng($im);
        }
        imagedestroy($im);
    }
}
?>
