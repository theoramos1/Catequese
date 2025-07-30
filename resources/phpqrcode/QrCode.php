<?php
namespace resources\phpqrcode;

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    throw new \RuntimeException('Dependencies missing. Run "composer install".');
}
require_once $autoload;

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
