<?php
namespace Yandex\Fotki;

class Encrypt
{
    public static function hex2dec($num)
    {
        $res = '0';
        $mul = '1';
        for ($i = strlen($num) - 1; $i >= 0; $i--) {
            $res = bcadd($res, bcmul(hexdec($num{$i}), $mul));
            $mul = bcmul($mul, 16);
        }
        return $res;
    }

    public static function dec2hex($num)
    {
        $ciphers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        $res = '';
        while ($num != '0') {
            $res = $ciphers[bcmod($num, 16)] . $res;
            $num = bcdiv($num, 16, 0);
        }
        return $res;
    }

    public static function str2arr($str)
    {
        return preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    public static function encryptPortion($portion, $n, $e)
    {
        $plain = '0';
        foreach (array_reverse($portion) as $k => $v) {
            $plain = bcadd($plain, bcmul($v, bcpowmod(256, $k, $n)));
        }
        $t = self::dec2hex(bcpowmod($plain, $e, $n));
        return $t;
    }

    public static function encrypt($key, $data)
    {
        list($nstr, $estr) = explode('#', $key);
        $ks = strlen($nstr) / 2;
        $n = self::hex2dec($nstr);
        $e = self::hex2dec($estr);
        $in_size = strlen($data);
        $data = array_map(function ($c) {
            return ord($c);
        }, self::str2arr($data));
        $portion_len = strlen($nstr) / 2 - 1;
        $prev_crypted = array_fill(0, $portion_len, 0);
        $out = "";
        while ($in_size) {
            $cur_size = $in_size > $portion_len ? $portion_len : $in_size;
            $portbuf = array();
            for ($i = 0; $i < $cur_size; $i++) {
                $portbuf[$i] = $data[$i] ^ $prev_crypted[$i];
            }
            $encrypted_portion = self::encryptPortion($portbuf, $n, $e);
            $encrypted_size = strlen($encrypted_portion);
            $out .= ($cur_size < 16 ? "0" : "") . dechex($cur_size) . "00";
            $out .= ($ks < 16 ? "0" : "") . dechex($ks) . "00";
            $out .= $encrypted_portion;
            $in_size -= $cur_size;
            for ($i = 0; $i < $encrypted_size; $i += 2)
                $prev_crypted[$i / 2] = hexdec('0x' . substr($encrypted_portion, $i, 2));
            $data = array_slice($data, $cur_size);
        }
        return base64_encode(pack("H*", $out));
    }
}
