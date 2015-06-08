<?php

@framework();

class OutputBuffer
{
	private $allBufferFilters  = array();
	private $gzipCompressState = true;
	private $minFileSizeToGzip = 0;
	private static $instance   = null;

	function __construct() {
		if (ob_get_level()){
			ob_end_clean();
		}

		ob_start(array($this, 'handler'));
		$config = Config::get('bbs.output_buff');
		$this->gzipCompressState = $config['gizp_state'];
		$this->minFileSizeToGzip = $config['gizp_min_size'];
	}


	public function createBuffer() {
		ob_start();
	}


	public function addFilter($filter) {
		$this->allBufferFilters[] = $filter;
	}


	public function removeFilter() {
		$c = count($this->allBufferFilters);
		for($i = 0; $i < $c; $i++) {
			if (is_callable($this->filters[$i]))
				unset($this->filters[$i]);
		}
	}


	public function getContents($clean = false) {
		return $clean ? ob_get_clean() : ob_get_contents();
	}


	public function handler($content) {
		foreach($this->allBufferFilters as $filter) {
			$content = $filter($content);
		}

		if (preg_match('/(gzip|deflate)/i', getenv('HTTP_ACCEPT_ENCODING')) && 
					strlen($content) > $this->minFileSizeToGzip && $this->gzipCompressState) {
			header('Vary: Accept-Encoding');
			header('Content-Encoding: gzip');
			header('Content-Length: '.ob_get_length());
			$content = gzencode($content, 4);
		}
		return $content;
	}


	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	public function getGzipState() {
		return $this->gizpCompressState;
	}
}