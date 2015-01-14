<?php
function form_years($name, $value = '') {
	$d = '<select name="'.$name.'">';

	for ($i=date('Y'); $i > (date('Y')-2); $i--) { 
		$d .= '<option value="'.$i.'" '.(($value == $i) ? 'selected' : '').'>'.$i.'</option>';
	}

	$d .= '</select>';

	return $d;
}

function form_months($name, $value = '') {
	$d = '<select name="'.$name.'">';

	for ($i=01; $i < 13; $i++) { 
		$i = str_pad($i, 2, 0, STR_PAD_LEFT);
		$d .= '<option value="'.$i.'" '.(($value == $i) ? 'selected' : '').'>'.$i.'</option>';
	}

	$d .= '</select>';

	return $d;
}