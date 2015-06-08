<?php

/**
 * The bootstrap file for boot initization.
 * Date: 2014/03/09 12:27 Sun.
 */


if (!defined('IN_FW')){
	define('IN_FW', true);
}

if (!defined('DIRECTORY_SEPARATOR')){
	define('DIRECTORY_SEPARATOR', '/');
}

// /Funwork directory path.
if (!defined('_FW_NAME_')) {
	define('_FW_NAME_', 'Funwork');
}

define('DS', DIRECTORY_SEPARATOR);
define('_ROOT_', dirname(dirname(__FILE__)));
define('_FW_', _ROOT_ . DS . _FW_NAME_);
define('_LIB_', _FW_ . DS . 'Lib');
define('_FUN_', _FW_ . DS . 'Func');
define('_CFG_', _FW_ . DS . 'Config');
define('_LOG_', _FW_ . DS . 'Log');
define('_PGE_', _FW_ . DS . 'Page');
define('_CRE_', _FW_ . DS . 'Core');
define('_HOK_', _FW_ . DS . 'Hook');
define('_ATL_', _FW_ . DS . 'Autoload');
define('_TMP_', _FW_ . DS . 'Temp');
define('_UPL_', _FW_ . DS . 'Upload');


define('_WEB_', _ROOT_. DS . 'Web');
define('_CSS_', _WEB_ . DS . 'Css');
define('_JS_',  _WEB_ . DS . 'Js');
define('_IMG_', _WEB_ . DS . 'Image');
define('_VEW_', _WEB_ . DS . 'View');
define('_TPL_', _VEW_ . DS . 'Template');
define('_CHE_', _VEW_ . DS . 'Cache');
define('_CPL_', _VEW_ . DS . 'Compiled');

// date and time.
define('DATE_FORMAT', 'Y/m/d');
define('TIME_FORMAT', 'H:i:s');
define('TIME_ZONE', 'Asia/Shanghai');

// File ext.
define('PHP_EXT', '.php');

// Router.
define('DEFAULT_PAGE', 'Home');

// Debugging.
define('IS_DEBUG', !isset($_REQUEST['no_debug']) ? true : false);
define('ERROR_REPORT_MODE', IS_DEBUG ? E_ALL : 0);
define('ERROR_IS_DISPLAY', IS_DEBUG ? 'On' : 'Off');

//Session id.
define('SID', isset($_REQUEST['sid']) && !empty($_REQUEST['sid']) ? $_REQUEST['sid'] : null);

define('IS_GET', $_SERVER['REQUEST_METHOD'] == 'GET');
define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST');

// Html
define('BR', '<br/>');
define('HR', '<hr/>');

