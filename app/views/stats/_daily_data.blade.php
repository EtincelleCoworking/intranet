<style type="text/css">
    table#stats-devices td, table#stats-devices th {
        font-size: 7pt;
    }

    table#stats-devices thead {
        display: block;
    }

    table#stats-devices tbody {
        display: block;
        height: 30em; /* 5 times the equivalent of a text "size". */
        overflow-y: scroll;
    }

    table#stats-devices thead tr th:nth-child(1),
    table#stats-devices tbody tr:first-child td:nth-child(1) { /* column 1 ! */
        width: 10em;
    }

    table#stats-devices thead tr th:nth-child(2),
    table#stats-devices thead tr th,
    table#stats-devices tbody tr td {
        width: 5em;
    }

    .date-active, .date-inactive, .date-weekend {
        border-left: 1px solid #ffffff;
    }

    .date-invalid {
        /*
        background: repeating-linear-gradient(
                -45deg,
                #ababab,
                #ababab 10px,
                #bcbcbc 10px,
                #bcbcbc 20px
        );
        */
        background-color: #666 !important;
    }

    .date-weekend {
        background-color: #E0E0E0 !important;
    }

    .date-active {
        background-color: #3DCF5F;
    }

    .date-inactive {
        background-color: #F5F5F5;
    }

    .date-active2 div {
        background-color: #f66a0a !important;
    }

    .date-active {
        background-color: #3DCF5F;
    }

    .date-active.date-weekend {
        background-color: #2cbe4e !important;
    }

</style>
<?php

if (count($days)) {
    asort($days);

    $min = $days[0];
    $max = $days[count($days) - 1];
} elseif (count($days2)) {
    asort($days2);

    $min = $days2[0];
    $max = $days2[count($days2) - 1];
} else {
    $min = date('Y-01-01');
}
    $max = date('Y-m-01');

$min_year = substr($min, 0, 4);
$min_month = substr($min, 5, 2);
$max_year = substr($max, 0, 4);
$max_month = substr($max, 5, 2);

$min_month = ($max_year == $min_year) ? $min_month : 1;

?>

<table class="table" id="stats-devices">
    <tbody>
    @for($year = $max_year,$month = $max_month; $year >= $min_year; $year--, $month = 12, $min_month = ($year == $min_year)?$min_month:1)
        @for(; $month >= $min_month; $month--)
            <tr>
                <td>{{sprintf('%02d/%04d', $month, $year)}}</td>
                @for($day = 1; $day <= 31; $day++)
                    <?php
                    $classes = array();
                    if (checkdate($month, $day, $year)) {
                        $current = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        if (in_array($current, $days)) {
                            $classes[] = 'active';
                        } else {
                            $classes[] = 'inactive';
                        }
                        if (in_array($current, $days2)) {
                            $classes[] = 'active2';
                        }
                        if (in_array(date('N', mktime(0, 0, 0, $month, $day, $year)), array(6, 7))) {
                            $classes[] = 'weekend';
                        }
                    } else {
                        $classes[] = 'invalid';
                    }

                    printf('<td class="date-%s"><div title="%s">&nbsp;</div></td>', implode($classes, ' date-'), sprintf('%02d/%02d/%04d', $day, $month, $year));
                    ?>
                @endfor
            </tr>
        @endfor
    @endfor
    </tbody>
</table>
