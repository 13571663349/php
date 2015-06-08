<?php

/*
 * For recording log.
 */


final class Log
{
	const LOG_FILE_EXT		= '.log';
	const LOG_FILE_PREFIX	= 'error_';
	const LOG_FILE_DIR		= _LOG_;

	private static $logFileName = '';

	public function __construct() {}

	public static function write($contents) {
		self::$logFileName = self::LOG_FILE_PREFIX . date('Y-m-d') . self::LOG_FILE_EXT;
		$file = fopen(self::LOG_FILE_DIR . DS . self::$logFileName, 'a+');
		fwrite($file, $contents);
		fclose($file);
	}

}