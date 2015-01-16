<?php
function form_years($name, $value = '') {
	$d = '<select name="'.$name.'" class="form-control col-md-4">';

	for ($i=date('Y'); $i > (date('Y')-2); $i--) {
		$d .= '<option value="'.$i.'" '.(($value == $i) ? 'selected' : '').'>'.$i.'</option>';
	}

	$d .= '</select>';

	return $d;
}

function form_months($name, $value = '') {
    $d = '<select name="'.$name.'" class="form-control col-md-4">';

    for ($i=01; $i < 13; $i++) {
        $i = str_pad($i, 2, 0, STR_PAD_LEFT);
        $d .= '<option value="'.$i.'" '.(($value == $i) ? 'selected' : '').'>'.$i.'</option>';
    }

    $d .= '</select>';

    return $d;
}
function form_days($name, $value = '') {
	$d = '<select name="'.$name.'" class="form-control col-md-4">';

	for ($i=01; $i < 32; $i++) {
		$i = str_pad($i, 2, 0, STR_PAD_LEFT);
		$d .= '<option value="'.$i.'" '.(($value == $i) ? 'selected' : '').'>'.$i.'</option>';
	}

	$d .= '</select>';

	return $d;
}