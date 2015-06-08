<?php

@framework();

class DataIterator implements Iterator {
	private $data  = null;
	private $valid = true;
	private $count = 0;
	private $model = null;

	function __construct(BaseModel $obj) {
		if (!$obj->pdoObj instanceof PDO || !$obj->pdoStatementObj instanceof PDOStatement)
			return;

		$this->model = $obj;
	}


	public function next() {
		try{
			if (($this->data = $this->model->fetch()) != false) {
				$this->valid = true;
				++$this->count;
			}
		}catch(PDOException $e){
			$this->valid = false;
		}

		return $this->count;
	}


	public function valid() {
		return $this->valid;
	}


	public function current() {
		return $this->data;
	}


	public function rewind() {
		return;
	}


	public function key() {
		return $this->count;
	}


	public function getCount() {
		return $this->count;
	}
}
