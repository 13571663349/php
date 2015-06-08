<?php

function B_no_caching(View $view, $block_contents, $vars) {
	echo sprintf('%s', $view->compile(View::COMPILE_STRING_CODE, '', $block_contents));
}

function B_no_compile(View $view, $block_contents, $vars) {
	echo $block_contents;
}