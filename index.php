<?php

/**
 * The single entry of website.
 * Date: 2014/03/08 00:00 Sta.
 **/

//Record app start time.

define('SCRIPT_START_TIME', microtime(true));

function framework(){}


require './Funwork/constans.php';
require './Funwork/Core/Core.php';

Core::getInstance()->run();
?>
