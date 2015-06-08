<?php

@framework();


class E_URL extends Exception {
	const E_400 = '请求异常！';
	const E_401 = '请求未授权！';
	const E_403 = '请求被禁止！';
	const E_402 = '需要付款！';
	const E_404 = '页面未找到！';
	const E_405 = 'HTTP方法不被允许！';
	const E_407 = '代理未认证！';
	const E_415 = '不支持请求实体的格式！';
	const E_500 = '内部服务器错误！';
	const E_501 = '服务器不支持请求的工具！';
	const E_502 = '错误网关！';
	const E_503 = '服务器无法处理请求！';


	public function __construct($text) {
		parent::__construct($text);
	}
}