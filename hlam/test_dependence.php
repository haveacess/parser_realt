<?php

/*
	This file need start in console.
	If in end not have errors, then all so good.

	This file contain:
	- filesystem
	- db
	- composer
	- google sheet

*/

require __DIR__ . '/vendor/autoload.php';

$client = new \Google_Client();
$client->setApplicationName('Cian parser');
$client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$client->setAuthConfig(__DIR__ . '/credentials.json');
$service = new Google_Service_Sheets($client);

include_once 'settings.php';
include_once 'logger.php';

include 'curl.php';
include_once 'database/connect.php'; //connect to db

$sql = "select count(id) cnt from cian_general_data";
$result = mysqli_query($GLOBALS['connect'],$sql); 
$cnt = mysqli_fetch_assoc($result)['cnt'];

$OFFSET = 0; $index = 1;

$ads_ids = [];
while ($OFFSET <=$cnt) {
	

	$sql = "select id from cian_general_data cg LIMIT 50 OFFSET {$OFFSET}";
	$result = mysqli_query($GLOBALS['connect'],$sql); 

	
	while ($array = mysqli_fetch_assoc($result)) { 
		$index++;
		$id = $array['id'];

		echo "id: {$id} \n";
		$ads_ids[] = $id;
	}

	$OFFSET += 50;

}

?>