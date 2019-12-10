<?php
/**
 * Created by PhpStorm.
 * User: shordeaux
 * Date: 08/08/2018
 * Time: 15:46
 */

class Utils
{
    public static function isFerian($date)
    {
        return in_array($date,
            array(
                // -- 2015
                '2015-01-01',
                '2015-05-01',
                '2015-05-08',
                '2015-05-14',
                '2015-05-25',
                '2015-07-14',
                '2015-08-15',
                '2015-11-01',
                '2015-11-11',
                '2015-12-25',
                // -- 2016
                '2016-01-01',
                '2016-03-28',
                '2016-05-01',
                '2016-05-05',
                '2016-05-16',
                '2016-07-14',
                '2016-08-15',
                '2016-11-01',
                '2016-11-11',
                '2016-12-25',
                // -- 2017
                '2017-01-01',
                '2017-04-17',
                '2017-05-01',
                '2017-05-08',
                '2017-05-25',
                '2017-06-05',
                '2017-07-14',
                '2017-08-15',
                '2017-11-01',
                '2017-11-11',
                '2017-12-25',
                // -- 2018
                '2018-01-01',
                '2018-04-02',
                '2018-05-01',
                '2018-05-08',
                '2018-05-10',
                '2018-05-21',
                '2018-07-14',
                '2018-08-15',
                '2018-11-01',
                '2018-11-11',
                '2018-12-25',
                // -- 2019
                '2019-01-01',
                '2019-04-22',
                '2019-05-01',
                '2019-05-08',
                '2019-05-30',
                '2019-06-10',
                '2019-07-14',
                '2019-08-15',
                '2019-11-01',
                '2019-11-11',
                '2019-12-25',
                // -- 2020
                '2020-01-01',
                '2020-04-13',
                '2020-05-01',
                '2020-05-08',
                '2020-05-21',
                '2020-06-01',
                '2020-07-14',
                '2020-08-15',
                '2020-11-01',
                '2020-11-11',
                '2020-12-25',
            ));
    }

    /**
     * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.7
     * @param string $hex Colour as hexadecimal (with or without hash);
     * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
     * @return string Lightened/Darkend colour as hexadecimal (with hash);
     */
    public static function colorLuminance($hex, $percent)
    {

        // validate hex string

        $hex = preg_replace('/[^0-9a-f]/i', '', $hex);
        $new_hex = '#';

        if (strlen($hex) < 6) {
            $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
        }

        // convert to decimal and change luminosity
        for ($i = 0; $i < 3; $i++) {
            $dec = hexdec(substr($hex, $i * 2, 2));
            $dec = min(max(0, $dec + $dec * $percent), 255);
            $new_hex .= str_pad(dechex($dec), 2, 0, STR_PAD_LEFT);
        }

        return $new_hex;
    }

    public static function convertDate($frenchValue)
    {
        $tokens = explode('/', $frenchValue);
        if (count($tokens) == 3) {
            return sprintf('%d-%d-%d',
                $tokens[2],
                $tokens[1],
                $tokens[0]
            );
        }
        return false;
    }
}