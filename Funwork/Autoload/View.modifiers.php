<?php

//View all variables modifiers.

function M_date_format($time, $format = '') {
	if (empty($format)) {
		$format = DATE_FORMAT .' '. TIME_FORMAT;
	}
	return date($format, $time);
}

function M_to_string($var) {
	return str_replace(array('\\', '\''), array('', ''), var_export($var, true));
}