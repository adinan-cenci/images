<?php
namespace AdinanCenci\Images;

class Helper 
{
    public static function colorToAlocate($color) 
    {
        $rgba = Helper::color($color);

        if (isset($rgba[3]) && $rgba[3] !== false) {
            $perc       = $rgba[3] / 1 * 100;
            $alpha      = ceil(127 - (127 / 100 * $perc));
            $rgba[3]    = $alpha;
        }

        return $rgba;
    }

    /**
     * Normalizes data.
     * Receives the representation of a color, be in rgb or 
     * hexadecimal, alpha channel or no and return a 4 item 
     * array ( red, green, blue, alpha )
     */
    public static function color($color) 
    {
        if (is_array($color) && count($color) <= 4) {
            return array_pad($color, 4, null);
        }

        if (is_string($color) && substr_count($color, ',')) {
            return Helper::readRgbColor($color);
        }

        if (is_string($color) && (substr_count($color, '#') || strlen($color) <= 9) ) {
            return Helper::readHexadecimalColor($color);
        }
        
        return array();
    }

    /**
     * Converts a string containing rgb values 
     * into an array of parameter that can be passed 
     * to imagecolorallocate
     * 
     * Accepts:
     * 255,255,255
     * 255,255,255,255
     * 
     * @param string $string rgb values separated with ,
     * @return array
     */
    public static function readRgbColor($string)
    {
        $match = preg_match('/([0-9]+),([0-9]+),([0-9]+)(,([0-9.]+))?/', $string, $matches);

        if (! $match) {
            return null;
        }

        $rgba = array(
            $matches[1],
            $matches[2],
            $matches[3],
        );


        if (isset($matches[4])) {

            $alpha = (float) $matches[5];

            if ($alpha > 1) {
                $alpha = $alpha / 255;
            }

            $rgba[3] = $alpha;
        } else {
            $rgba[3] = false;
        }

        return $rgba;
    }

    /**
     * Converts a string containing an hexadecimal 
     * representation of a color into an array of 
     * parameters that can be passed to 
     * imagecolorallocate
     * 
     * Accepts:
     * 000        shorthand
     * #000       hash prefixed
     * 000000     full
     * 000000ff   full + alpha
     * #000000    hash prefixed
     * #000000ff  hash prefixed + alpha
     * 
     * @param string $hexadecimal Hexadecimal color representation
     * @return array
     */
    public static function readHexadecimalColor($hexadecimal)
    {
        $hexadecimal = trim($hexadecimal, '#');

        if (strlen($hexadecimal) == 3) {
            $red = substr($hexadecimal, 0, 2);
            $hex = array_fill(0, 3, $red);
        } else {
            $hex    = str_split($hexadecimal, 2);
        }

        $rgba   = array_map('hexdec', $hex);

        if (isset($rgba[3])) {
            $perc    = ($rgba[3] / 255) * 100;
            $rgba[3] = 0.01 * $perc;
        } else {
            $rgba[3] = false;
        }

        return $rgba;
    }

    public static function getFraction($value, $numerator, $denominator) 
    {
        return ($value / $denominator) * $numerator;
    }

    public static function pointToPixel($points)
    {
        return $points * 1.333333;
    }

    /**
     * @asserts ('twoThirds') = 2, 3
     * @asserts ('oneFourth') = 1, 4
     * @asserts ('twoSixths') = 2, 6
     */
    public static function parseFraction($str) 
    {
        if ($str == 'half') {
            $str = 'oneHalf';
        }

        if (preg_match('/halfA?([A-Za-z]+)/', $str, $matches)) {
            $numeratorS     = 'half';
            $denominatorS   = $matches[1];
        } else {

            preg_match('/([a-z]+)([A-Za-z]+)/', $str, $matches);

            if (empty($matches[1]) || empty($matches[2])) {
                return null;
            }

            $numeratorS     = $matches[1];
            $denominatorS   = $matches[2];
        }

        //---------        

        $numerator      = Helper::textToNumber($numeratorS);

        $denominator    = strtolower($denominatorS);
        $denominator    = rtrim($denominator, 's');
        $denominator    = Helper::textToNumber($denominator);

        if (!$numerator || !$denominator) {
            return null;
        }

        //---------

        if ($numeratorS == 'half') {
            $numerator = $denominator / 2;
        }

        //---------

        return array(
            'numerator'    => $numerator, 
            'denominator'  => $denominator
        );
    }

    public static function textToNumber($str) 
    {
        $ar = array(
            'one'       => 1, 
            'two'       => 2, 
            'half'      => 2, 
            'three'     => 3, 
            'third'     => 3, 
            'four'      => 4, 
            'fourth'    => 4, 
            'five'      => 5, 
            'fifth'     => 5, 
            'six'       => 6, 
            'sixth'     => 6, 
            'seven'     => 7, 
            'seventh'   => 7, 
            'eight'     => 8, 
            'eighth'    => 8, 
            'nine'      => 9, 
            'nineth'    => 9, 
            'ten'       => 10, 
            'tenth'     => 10, 
            'eleven'    => 11, 
            'eleventh'  => 11, 
            'twelve'    => 12, 
            'twelfth'   => 12
        );

        return isset($ar[$str]) ? $ar[$str] : null;
    }
}
