<?php
namespace resources\phpqrcode;

require_once __DIR__ . '/../../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class QrCode {
    public static function png(string $data, ?string $file = null, int $size = 300, int $margin = 0): void {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->size($size)
            ->margin($margin)
            ->build();

        if ($file) {
            $result->saveToFile($file);
        } else {
            echo $result->getString();
        }
    }
}
?>
