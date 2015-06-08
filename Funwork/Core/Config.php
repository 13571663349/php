<?php

//@framework();

class Config
{
	public static $configs  = array();
	public static $instance = null;

	public static function load() {
		$cfgs = array();
		foreach(glob(_CFG_. DS. '*.php') as $cfg) {
			$cfgs = array_merge($cfgs, include($cfg));
		}
		self::$configs = $cfgs;
		$_ENV = $cfgs;
	}

	public static function get($key) {
		if (strpos($key, '.') !== false) {
			$key = explode('.', $key);
			$tmp_var = self::$configs;
			foreach($key as $key_name) {
				if (isset($tmp_var[$key_name])) {
					$tmp_var = $tmp_var[$key_name];
				}
			}
			return $tmp_var;
		}

		if (isset(self::$configs[$key])) {
			return self::$configs[$key];
		}
	}


	public static function set($key, $value) {
		if (strpos('.', $key) !== false) {
			$key = '[' . implode('][', explode('.', $key)) . ']';
			eval("self::\$configs{$key} = $value;");
			return;
		}

		self::$configs[$key] = $value;
	}


	public static function getAll() {
		return self::$configs;
	}


	public static function save($file = 'coustom.php') {
		return file_put_contents($file, var_export($this->configs, true));
	}
}