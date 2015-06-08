<?php

/*
 * All view function declare.
 */

function N_include(View $view, $args) {
	$file = $args['file'];
	array_shift($args);
	$view->assignVariable($args);
	$view->includeView($file);
}

function N_assign(View $view, $vars) {
	$view->assignVariable($vars);
}


function N_total_used_time(View $view) {
	echo round((microtime(true) - SCRIPT_START_TIME) * 1000, 2);
}


function N_view_used_time(View $view) {
	echo round((microtime(true) - $view->startTime) * 1000, 2);
}

