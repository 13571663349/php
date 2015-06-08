<?php

/*
 * Common function library.
 * Date: 2014/03/15 Sta.
 */

function set_timezone()
{
	date_default_timezone_set('PRC');
}


function time_to_text($time)
{
	if ($time == 0) return false;

	$time = (string)abs($time);
	$date = array('秒', '分钟', '小时', '个月', '天', '年');
	$len  = strlen($time); $times = intval($len / 2);
	$mod  = $len % 2;
	$msg  = '';

	for ($i = 0; $i < $times + $mod; $i++) {
		$start  = -2 * ($i + 1);
		$msg	= intval(substr($time, $start, -$start > $len ? 1 : 2)). $date[$i]. $msg;
	}
	return $msg;
}


function parse_var($var_str, $sepator = '\s') {
	if (empty($var_str)) {
		return array();
	}

	parse_str(preg_replace_callback('/"((?>[^"\\\\]+|\\\\\")*)"('.$sepator.'+)?/', function($mt) {
			return isset($mt[2]) ? $mt[1] . '&' : $mt[1];
	}, $var_str), $vars);
	return $vars;
}
		

