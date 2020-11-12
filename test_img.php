<?php

include_once  __DIR__ . '/curl.php';

include_once   __DIR__ . '/config/settings.php';
include_once __DIR__ . '/database.php';
include_once __DIR__ . '/logs.php';
include_once   __DIR__ . '/images.php';

$mode = "test"; //Режим работы test || production
$settings = new Parser\Settings($mode);
$images = new Parser\Images($settings->path['images']);

$old_photos = [

	[
		'source' => 'avito',
		'id' => 3444817,
		'photos' => [
			"https://70.img.avito.st/640x480/9374966770.jpg",
			"https://89.img.avito.st/640x480/9374963589.jpg",
			"https://44.img.avito.st/640x480/9374965344.jpg",
			"https://46.img.avito.st/640x480/9374965246.jpg",
			"https://94.img.avito.st/640x480/9374963594.jpg"
		]
	]

];
$images->download($old_photos); 

//---------
// $new_photos = [
// 	[
// 		"id" => 3444817,
// 		"source" => "avito"
		
// 	]

// ];

// $images->remove($new_photos); //Удаляем папки

?>