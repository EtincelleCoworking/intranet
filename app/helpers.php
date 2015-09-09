<?php
function form_years($name, $value = '')
{
    $d = '<select name="' . $name . '" class="form-control col-md-4">';

    for ($i = date('Y'); $i > (date('Y') - 2); $i--) {
        $d .= '<option value="' . $i . '" ' . (($value == $i) ? 'selected' : '') . '>' . $i . '</option>';
    }

    $d .= '</select>';

    return $d;
}

function form_months($name, $value = '')
{
    $d = '<select name="' . $name . '" class="form-control col-md-4">';

    for ($i = 01; $i < 13; $i++) {
        $i = str_pad($i, 2, 0, STR_PAD_LEFT);
        $d .= '<option value="' . $i . '" ' . (($value == $i) ? 'selected' : '') . '>' . $i . '</option>';
    }

    $d .= '</select>';

    return $d;
}

function form_days($name, $value = '')
{
    $d = '<select name="' . $name . '" class="form-control col-md-4">';

    for ($i = 01; $i < 32; $i++) {
        $i = str_pad($i, 2, 0, STR_PAD_LEFT);
        $d .= '<option value="' . $i . '" ' . (($value == $i) ? 'selected' : '') . '>' . $i . '</option>';
    }

    $d .= '</select>';

    return $d;
}

function newDate($s)
{
    $date = explode('/', $s);
    return new \DateTime(sprintf('%d-%d-%d 00:00:00', $date[2], $date[1], $date[0]));
}


function newDateTime($date, $time)
{
    $date = explode('/', $date);
    $time = explode(':', $time);
    return new \DateTime(sprintf('%d-%d-%d %02d:%02d:00', $date[2], $date[1], $date[0], $time[0], $time[1]));
}

function getDuration($start, $end)
{
    $start = explode(':', $start);
    $end = explode(':', $end);
    return 60 * $end[0] + $end[1] - 60 * $start[0] - $start[1];

}

function durationToHuman($minutes)
{
    $hours = floor($minutes / 60);
    $minutes = $minutes % 60;
    $result = '';
    if ($hours) {
        $result = $hours . Lang::choice('messages.times_hours', $hours);
    }

    if ($minutes) {
        if ($result) {
            $result .= ' ';
        }
        $result .= $minutes . Lang::choice('messages.times_minutes', $minutes);
    }
    return $result;
}
//
//function durationToHumanShort($minutes)
//{
//    $hours = floor($minutes / 60);
//    $minutes = $minutes % 60;
//    if ($hours) {
//        return $hours . 'h';
//    }
//
//    if ($minutes) {
//        return $minutes . 'min';
//    }
//    return false;
//}

function adjustBrightness($hex, $steps)
{
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color = hexdec($color); // Convert to decimal
        $color = max(0, min(255, $color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}

function hexColorToRgbWithTransparency($color, $transparency)
{
    $color = str_replace("#", "", $color);

    if (strlen($color) == 3) {
        $r = hexdec(substr($color, 0, 1) . substr($color, 0, 1));
        $g = hexdec(substr($color, 1, 1) . substr($color, 1, 1));
        $b = hexdec(substr($color, 2, 1) . substr($color, 2, 1));
    } else {
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
    }
    return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, $transparency);
}