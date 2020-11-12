<?php

include_once  __DIR__ . '/curl.php';

include_once   __DIR__ . '/config/settings.php';
include_once __DIR__ . '/database.php';
include_once __DIR__ . '/logs.php';
include_once   __DIR__ . '/images.php';


$mode = "test"; //Режим работы test || production
$settings = new Parser\Settings($mode);

$time = strftime("%d %B %Y - %H:%M", 1602761985); // получаем 15 oct. 14:39
echo "time: {$time}";

?>