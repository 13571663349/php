<?php

@framework();

abstract class BasePage
{
	protected $core;
	protected $view;
	protected $model;
	protected $config;
	protected $router;


	function __construct(Core $core, Router $router) {
		$this->core	  = $core;
		$this->view   = View::getInstance();
		$this->model  = Model::getInstance();
		$this->config = Config::getAll();
		$this->router = $router;
	}


	protected function init() {
		$this->model->connect();
		$sessInfo = $this->config['session'];
		session_name('sid');
		session_set_cookie_params($sessInfo['life_time'], $sessInfo['path'],
			$sessInfo['domain'], $sessInfo['secure'], $sessInfo['http_only']);

		register_shutdown_function('session_write_close');
		session_start();
	}


	protected function checkUserStatus() {
		//if ($this->model->isGuest())
			return;

		//$this->model->updateUsersStatus();
	}


	public function onGetRequest() {
		$this->init();
		$this->checkUserStatus();
		$this->showView();
	}


	public function onPostRequest() {
		$this->init();
		$this->checkUserStatus();
		$this->showView();
	}


	public function onUploadRequest() {
		$this->init();
		$this->checkUserStatus();
	}


	protected function showView($arg1 = null, $arg2 = null, $arg3 = null) {
		$this->view->show(strtolower(get_class($this)). View::TEMPLATE_EXT, $arg1, $arg2, $arg3);
	}


	function __destruct() {
		//$this->model->updateUsersStatus();
		unset($this);
	}
}