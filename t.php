<?php
header('Content-Type: audio/mpeg');
readfile(iconv('UTF-8', 'GBK', 'F:\\我的音乐\\' . urldecode($_GET['m'])));