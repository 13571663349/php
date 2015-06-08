<?php

@framework();

class Router
{
	protected $pageID	= '';
	protected $doAction	= '';
	protected $method	= '';
	protected $isUpload	= '';
	protected $queryStr = '';

	private static $instance = null;


	function __construct() {
		$this->init();
		$this->parseUrl();
	}


	private function parseUrl() {
	
	}


	private function init() {
		$this->method   = $_SERVER['REQUEST_METHOD'];
		$this->isUpload = empty($_FILES) == false;
		$this->pageID	= isset($_GET['pid']) ? $_GET['pid'] : null;
		$this->doAction	= isset($_GET['do']) ? $_GET['do'] : null;
	}


	public function getPage() {
		return $this->pageID ? $this->pageID : DEFAULT_PAGE;
	}


	public function getAction() {
		return $this->doAction;
	}


	private function isUpload() {
		return $this->isUpload;
	}

	public function dispatch(Core $core) {
		$pageID = _PGE_ . DS . $this->getPage(). PHP_EXT;
		if (!is_file($pageID) || !is_readable($pageID))
			throw new E_URL(E_URL::E_404);

		$pageID = $this->getPage();
		$pageID = new $pageID($core, $this);
		
		switch($this->method) {
			case 'GET':
				$pageID->onGetRequest();
				break;
			case 'POST':
				if ($this->isUpload()) {
					$pageID->onUploadRequest();
				}else{
					$pageID->onPostRequest();
				}
				break;
			default:
				throw new E_URL(E_URL::E_500);
		}
	}


	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __destruct() {
		unset($this);
	}
}