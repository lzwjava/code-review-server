<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/30
 * Time: 上午12:29
 */
class ColorConvert
{
    private function hexToDecimal($hex)
    {
        return (int)base_convert($hex, 16, 10);
    }

    public function stringToColorCode($str)
    {
        $code = dechex(crc32($str));
        $code = substr($code, 0, 6);
        $hue = substr($code, 0, 2);
        $saturation = substr($code, 2, 2);
        $brightness = substr($code, 4, 2);
        $hue = $this->hexToDecimal($hue);
        $saturation = $this->hexToDecimal($saturation);
        $brightness = $this->hexToDecimal($brightness);

        $hue = round(($hue / 255.0) * 360);
        $saturation = $saturation % 50 + 50;
        $brightness = $brightness % 50 + 50;
        $rgb = $this->hsb2rgb($hue, $saturation, $brightness);
        return $rgb;
    }

    public function hsb2rgb($h, $s, $b)
    {
        $ret = $this->hsb2rgbArr($h, $s, $b);
        if ($ret === false) return "#000";
        $r = dechex($ret['red']);
        if (strlen($r) === 1) $r = "0" . $r;
        $g = dechex($ret['green']);
        if (strlen($g) === 1) $g = "0" . $g;
        $b = dechex($ret['blue']);
        if (strlen($b) === 1) $b = "0" . $b;
        return $r . $g . $b;
    }

    private function hsb2rgbArr($hue, $saturation, $brightness)
    {
        $hue = $this->significantRound($hue, 3);
        //echo $hue;
        if ($hue < 0 || $hue > 360) {
            throw new LengthException('Argument $hue is not a number between 0 and 360');
        }
        $hue = $hue == 360 ? 0 : $hue;
        $saturation = $this->significantRound($saturation, 3);
        if ($saturation < 0 || $saturation > 100) {
            throw new LengthException('Argument $saturation is not a number between 0 and 100');
        }
        $brightness = $this->significantRound($brightness, 3);
        if ($brightness < 0 || $brightness > 100) {
            throw new LengthException('Argument $brightness is not a number between 0 and 100.');
        }
        $hexBrightness = (int)round($brightness * 2.55);
        if ($saturation == 0) {
            return array('red' => $hexBrightness, 'green' => $hexBrightness, 'blue' => $hexBrightness);
        }
        $Hi = floor($hue / 60);
        $f = $hue / 60 - $Hi;
        $p = (int)round($brightness * (100 - $saturation) * .0255);
        $q = (int)round($brightness * (100 - $f * $saturation) * .0255);
        $t = (int)round($brightness * (100 - (1 - $f) * $saturation) * .0255);
        switch ($Hi) {
            case 0:
                return array('red' => $hexBrightness, 'green' => $t, 'blue' => $p);
            case 1:
                return array('red' => $q, 'green' => $hexBrightness, 'blue' => $p);
            case 2:
                return array('red' => $p, 'green' => $hexBrightness, 'blue' => $t);
            case 3:
                return array('red' => $p, 'green' => $q, 'blue' => $hexBrightness);
            case 4:
                return array('red' => $t, 'green' => $p, 'blue' => $hexBrightness);
            case 5:
                return array('red' => $hexBrightness, 'green' => $p, 'blue' => $q);
        }
        return false;
    }

    private function significantRound($number, $precision)
    {
        if (!is_numeric($number)) {
            throw new InvalidArgumentException('Argument $number must be an number.');
        }
        if (!is_int($precision)) {
            throw new InvalidArgumentException('Argument $precision must be an integer.');
        }
        return round($number, $precision - strlen(floor($number)));
    }
}