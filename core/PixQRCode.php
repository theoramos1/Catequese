<?php
namespace catechesis;

require_once(__DIR__.'/Configurator.php');
require_once(__DIR__.'/../resources/phpqrcode/QrCode.php');

use resources\phpqrcode\QrCode;
use Exception;

class PixQRCode{
    private static function tlv(string $id, string $value): string{
        return $id . str_pad(strlen($value), 2, '0', STR_PAD_LEFT) . $value;
    }

    private static function crc16(string $data): string{
        $crc = 0xFFFF;
        $polynomial = 0x1021;
        for($i = 0; $i < strlen($data); $i++){
            $crc ^= ord($data[$i]) << 8;
            for($b = 0; $b < 8; $b++){
                if($crc & 0x8000){
                    $crc = ($crc << 1) ^ $polynomial;
                }else{
                    $crc <<= 1;
                }
                $crc &= 0xFFFF;
            }
        }
        return strtoupper(sprintf('%04X', $crc));
    }

    public static function buildPayload(string $key, string $merchantName, string $merchantCity, string $txid, ?float $amount, string $description = ''): string{
        $payload  = self::tlv('00', '01');
        $payload .= self::tlv('01', '12');

        $mai  = self::tlv('00', 'br.gov.bcb.pix');
        $mai .= self::tlv('01', $key);
        if($description !== ''){
            $mai .= self::tlv('02', $description);
        }
        $payload .= self::tlv('26', $mai);
        $payload .= self::tlv('52', '0000');
        $payload .= self::tlv('53', '986');
        if($amount !== null){
            $payload .= self::tlv('54', number_format($amount, 2, '.', ''));
        }
        $payload .= self::tlv('58', 'BR');
        $payload .= self::tlv('59', substr($merchantName, 0, 25));
        $payload .= self::tlv('60', substr($merchantCity, 0, 15));
        $payload .= self::tlv('62', self::tlv('05', $txid));
        $payload .= '6304';
        $payload .= self::crc16($payload);
        return $payload;
    }


    public static function generatePixQRCode(?float $amount): string{

        $key  = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_KEY);
        $name = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_RECEIVER);
        $city = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_CITY);
        $desc = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_DESCRIPTION) ?? '';
        $txid = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_TXID) ?? '***';

        if(!$key || !$name || !$city){
            throw new Exception('Pix configuration incomplete');
        }

        $payload = self::buildPayload($key, $name, $city, $txid, $amount, $desc);

        ob_start();
        QrCode::png($payload, null, 300, 0);
        $data = ob_get_clean();
        return 'data:image/png;base64,' . base64_encode($data);
    }

    public static function generatePixPayload(?float $amount): string{
        $key  = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_KEY);
        $name = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_RECEIVER);
        $city = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_CITY);
        $desc = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_DESCRIPTION) ?? '';
        $txid = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_TXID) ?? '***';

        if(!$key || !$name || !$city){
            throw new Exception('Pix configuration incomplete');
        }

        return self::buildPayload($key, $name, $city, $txid, $amount, $desc);
    }
}
?>
