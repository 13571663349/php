<?php

/*
 * The funwork core class.
 * 2014-08-17 15:51
 */

@framework();

require 'Config.php';

final class Core
{
	public $config			= null;
	public $outputBuffer 	= null;

	private static $instance= null;


	private function __construct() {
		Config::load();
		$this->init();
	}


	private function init() {
		$this->config = Config::getAll();
		$this->registerClassLoader();
		$this->loadAutoload();
		$this->loadFunctions();
		$this->setExceptionHandler();
		$this->setErrorHandler();
		$this->outputBuffer = OutputBuffer::getInstance();

		set_timezone();
		error_reporting(ERROR_REPORT_MODE);
		ini_set('display_errors', ERROR_IS_DISPLAY);

		$sessInfo = $this->config['session'];
		ini_set('session.gc_probability', $sessInfo['gc_probability']);
		ini_set('session.gc_divisor',	  $sessInfo['gc_divisor']);
		ini_set('session.gc_maxlifetime', $sessInfo['gc_maxlifetime']);

		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');
	}


	public function run() {
		$router = Router::getInstance();
		$router->dispatch($this);
	}


	private function registerClassLoader() {
		spl_autoload_register(array($this, 'autoloader'));
	}


	public function autoloader($cls) {
		$class_path = $this->config['class_path'];
		foreach($class_path as $path) {
			$_file = $path. DS. $cls. PHP_EXT;
			if (is_file($_file)) {
				require($_file);
				break;
			}
		}
	}


	private function setExceptionHandler() {
		set_exception_handler(array($this, 'exceptionHandler'));
	}


	private function setErrorHandler() {
		set_error_handler(array($this, 'errorHandler'));
	}

	private function loadFunctions() {
		$arr = glob(_FUN_. DS. '*.php');
		foreach($arr as $file) {
			require $file;
		}
	}


	private function loadAutoload() {
		foreach(glob(_ATL_. DS. '*.php') as $file) {
			require $file;
		}
	}


	public function exceptionHandler($e) {
		if ($e instanceof ErrorException) {
			$error_text = "Get a error: %s in %s at %d line\n";
		}else{
			$error_text = "Get a exception: %s in %s at %d line\n";
		}


		echo sprintf('<pre> %s </pre>', print_r(array(
			'msg'  => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
			'code' => $e->getCode(),
			'trace'=> $e->getTraceAsString()
			), true
		));
		Log::write(sprintf($error_text, $e->getMessage(), $e->getFile(), $e->getLine()));
	}


	public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}


	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	private function __destroy() {
		unset($this);
		exit;
	}


	public function isDebug() {
		return IS_DEBUG;
	}
}