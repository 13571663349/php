<?php

class File
{
	const  R_LOCK			= 0;
	const  W_LOCK			= 1;

	protected $fileName		= '';
	protected $length		= 0;
	protected $handler		= null;
	protected $openMode		= '';
	protected $isLock		= false;
	protected $lockMode		= array();

	public function __construct($file_name, $mode = 'r', $lock_mode = LOCK_SH) {
		if (empty($file_name)) {
			throw new FileNotFoundException($file_name);
		}

		$this->openMode	= $mode;
		$this->lockMode = $lock_mode;
		$this->fileName	= $file_name;
	}


	public function open() {
		$this->handler = fopen($this->fileName, $this->openMode);
		if (!is_resource($this->handler)) {
			throw new IOException();
		}

		return true;
	}


	public function read($count) {
		$buff = fread($this->handler, $count);
		return $buff;
	}


	public function readAll() {
		$buff = '';
		while(!feof($this->handler)) {
			$buff .= fread($this->handler, 4096);
		}

		return $buff;
	}


	public function lock($type = self::R_LOCK) {
		$f = flock($this->handler, $this->lockMode[$type]);

		if (!$f && $type == self::R_LOCK) {
			return false;
		}else if (!$f && $type == self::W_LOCK) {
			sleep(3);
			if (flock($this->handler, $this->lockMode[$type])) {
				return true;
			}
		}
		return false;
	}


	public function write($content) {
		return fwrite($this->handler, $content);
	}


	public function seek($offset) {
		return fseek($this->handler, $offset);
	}


	public function length() {
		return filesize($this->name);
	}


	public function delete() {
	}


	public function __destruct() {
		if (is_resource($this->handler)) {
			fclose($this->handler);
		}
		unset($this);
	}
}

