<?php

return array
(
	'bbs' => array(
		'name'			=> '风铃论坛',
		'css_path'		=> _CSS_,
		'js_path'		=> _JS_,
		'copyright'		=> '&copy;Windy Bells 2014 ~ 2015',
		'output_buff'	=> array(
			'gizp_state' 	=> true,
			'gizp_min_size'	=> 1024 * 1024 * 20,
		),
	),

	'page' => array(
		'default_css' => 'default.css',
		'default_js'  => 'default.js',
		'style'		  => 'default',
		'charset'	  => 'UTF-8',
		'keyword'	  => 'Web,Js,Css,HTML,PHP,C,Java,C++,Linux,Windows',
		'viewport'	  => '',
		'description' => '这是一个绿色，免费，自由的论坛，来这里和大家一起交流吧！',
	),

	'mysql' => array(
		'host'		=> getenv('HTTP_HOST'),
		'dbname'	=> 'main',
		'user' 		=> 'root',
		'pass'	 	=> '7878031719',
		'prefix' 	=> 'wb_',
		'attr'		=> array(
			PDO::ATTR_DEFAULT_FETCH_MODE		=> PDO::FETCH_ASSOC,
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY 	=> true,
			PDO::ATTR_PERSISTENT				=> true
		)
	),
	
	'http_status' => array(
		400 => '请求异常！',
		401 => '请求未授权！',
		403 => '请求被禁止！',
		402 => '需要付款！',
		404 => '页面未找到！',
		405 => 'HTTP方法不被允许！',
		407 => '代理未认证！',
		415 => '不支持请求实体的格式！',
		500 => '内部服务器错误！',
		501 => '服务器不支持请求的工具！',
		502 => '错误网关！',
		503 => '服务器无法处理请求！'
	), 
	
	'class_path' => array(
		_CRE_,
		_LIB_,
		_PGE_,
		_HOK_,
	),

	'error_type' => array (
		E_ERROR					=> 'ERROR',
		E_WARNING				=> 'WARNING',
		E_PARSE					=> 'PARSING ERROR',
		E_NOTICE				=> 'NOTICE',
		E_CORE_ERROR			=> 'CORE ERROR',
		E_CORE_WARNING			=> 'CORE WARNING',
		E_COMPILE_ERROR			=> 'COMPILE ERROR',
		E_COMPILE_WARNING		=> 'COMPILE WARNING',
		E_USER_ERROR			=> 'USER ERROR',
		E_USER_WARNING			=> 'USER WARNING',
		E_USER_NOTICE			=> 'USER NOTICE',
		E_STRICT				=> 'STRICT NOTICE',
		E_RECOVERABLE_ERROR		=> 'RECOVERABLE ERROR'
	),

	'session' => array(
		'gc_probability'	=> 50,
		'gc_divisor'		=> 100,
		'gc_maxlifetime'	=> 604800,
		'life_time'			=> 604800,
		'path'				=> '/',
		'domain'			=> getenv('HTTP_HOST'),
		'secure'			=> false,
		'http_only'			=> false
	),

	'user' => array(
		'max_no_action_time' => 500, // hhmmss
		'online_update_time' => 300, // Secondes
	),


	'view' => array(
		'cache_dir'		=> _CHE_,
		'compiled_dir'	=> _CPL_,
		'template_dir'	=> _TPL_,
		'cache_time'	=> 600,
	)
);