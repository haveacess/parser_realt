<?php

require __DIR__ . '/vendor/autoload.php';

$client = new \Google_Client();
$client->setApplicationName('Cian parser');
$client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$client->setAuthConfig(__DIR__ . '/credentials.json');
$service = new Google_Service_Sheets($client);

/* General code for parsing*/

include_once 'settings.php';
include_once 'logger.php';

include 'curl.php';
include_once 'database/connect.php'; //connect to db


add_logs("Parsing is started");


$endpoints = [
	'list' => 'https://api.cian.ru/search-engine/v1/search-offers-mobile-site/',
	'page' => 'https://api.cian.ru/offer-card/v1/get-offer-mobile-site/?dealType=sale&offerType=flat&cianId=%s&noRedirect=true&subdomain=www'

];

/* Code for test here */

//show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//show errors

get_removed_status();



exit();

// $a = info_ad('234080737', $GLOBALS['endpoints']['page']);
// var_dump($a);
// exit();

// $b = send_post('http://cian.ru/sale/flat/218676829', ['g-recaptcha-response' => '03AGdBq27OwCoY9F9ZRug3by6Bqw-fznpFDaiQOTKfAAr6RjSFAii5lq126VHH7P3mmFW_fxez_r2ppEF_gPqjVqpCwIdgk6tKt2CKk8zjim6puNJHCtTT6XO7o77H_Zpx2kqM3pkF344XNd62vgEISqyUuV7rukKRrxzeev5UOSBgVJYOY5y9ZzPg5_bSCVFUHJccgNDuckQyghiykhihlIssXszimFJ3NM84lM6XgnkNwpKA7oPXK6XLQvCR47FM6gJlSrJbM72LR901kYnh85W0uOd7hH1XN4F9IgCCC30WeskDaAI1v_KnDOnrsQ9ZOdO61PD1fItfcWO_c-V9T8ykXwNHE-2703I16XhOZAY0gzgaf9ZU2TwMMVgMjfMfN3-cpsLNuusZjxh__3WBl16Y_hhwdGBsNJ6lfOERN5yA9mJeS3HJ2kT_FR3_q22_WQHJ_PFGPfSKbUGTRUS0t8lZOcKo0Lthrg'], ["Content-Type: application/x-www-form-urlencoded"]);

// var_dump($b);

// exit();

// $OFFSET = 955; $index = 956;

// $sql = "select id from cian_general_data cg LIMIT 50 OFFSET {$OFFSET}";
// $result = mysqli_query($GLOBALS['connect'],$sql); 

// $ids = [];

// while ($array = mysqli_fetch_assoc($result)) {

// 	$id = $array['id'];
// 	$ids[] = $id;

// }

// foreach ($ids as $id) {
// 	sleep(1);
// 	$index++;
// 	echo "id: {$id} \n";

// 	echo "index: {$index}. id: {$id} \n";

// 	$a = info_ad($id, $GLOBALS['endpoints']['page']);

// 	if ($a == null) {

// 		//update with_photos = 0;
// 		mysqli_query($GLOBALS['connect'],"update cian_general_data set with_photos=0 where id={$id}");
// 		continue;
// 	}

// 	mysqli_query($GLOBALS['connect'],"update cian_general_data set with_photos=1 where id={$id}");

// 	$photos = $a['constant']['photos'];
// 	//var_dump($photos);

// 	$path = "images/{$id}/";
// 	mkdir($path);

// 	$i = 0;
// 	foreach ($photos as $object) {
// 		if ($object['type'] == 'photo' || $object['type'] == 'layout') {
// 			$i++;
// 			$path = "images/{$id}/{$i}.jpg";

// 			echo "path: {$path} \n";
// 			echo "url: {$object['previewUrl']} \n";

// 			$photo = file_get_contents($object['previewUrl']);
// 			file_put_contents($path, $photo);

// 		}
// 	}
// }


// while ($array = mysqli_fetch_assoc($result)) { 

// 	$index++;

	
	

	
// }


// foreach ($ads_ids as $key) {
// 	$a = info_ad($key, $GLOBALS['endpoints']['page']);

// 	if ($a != null) {
// 		$phones = $a['variable']['phones'];
// 		$id = $key;

// 		echo "phones: {$phones}. id: {$id} \n";

// 		mysqli_query($GLOBALS['connect'],"update cian_general_data SET phones='{$phones}' WHERE id={$id}");
// 	}
// }


// foreach ($ads_ids as $key) {

// 	echo "key: {$key} \n";

// 	$a = info_ad($key, $GLOBALS['endpoints']['page']);
// 	var_dump($a);
// 	$attr_c = $a['constant'];
// 	$attr_v = $a['variable'];

// 	//update price_actual && price_start
// 	mysqli_query($GLOBALS['connect'],"update cian_general_data SET price_actual={$attr_v['price_actual']}, price_start={$attr_v['price_start']} WHERE id={$key}");
	



// 	if (count($attr_c['photos']) > 0) {

// 		echo "load photo. key: {$key} \n";

// 		for ($i=0;$i<count($attr_c['photos']); $i++) { 

// 			$path = "images/{$key}/";
// 			mkdir($path);

// 			for ($i=0;$i<count($attr_c['photos']); $i++) { 

// 				if ($attr_c['photos'][$i]['type'] == 'photo' || $attr_c['photos'][$i]['type'] == 'layout') {
// 					$path = "images/{$key}/{$i}.jpg";
// 					$photo = file_get_contents($attr_c['photos'][$i]['previewUrl']);
// 					file_put_contents($path, $photo);
// 				}
// 			}
// 		}

// 	}
// 	else {
// 		echo "photo not found. key: {$key} \n";	
// 		//update with_photos = 0
// 		mysqli_query($GLOBALS['connect'],"update cian_general_data set with_photos=0 where id={$key}");
// 	}


// }

/* Code for test here */



mysqli_close($GLOBALS['connect']); //close connect
add_logs("Parsing is finished");

exit();


/* getting market price ad */
function get_market_price($cianId) {

	$price = 0;
	$cnt = 0;
	$accuracy = 0;

	//--------------------------------
	//Точность зависит от кол-ва транзакций и как далеко мы уточнили критерии поиска (до дома квартиры и тд)

	//1 ищем тип квартиры такой же как и у нашей. выоводим ср. цену рыночную и кол-во сделок
	//точность = 50%
	//цена = средняя цена
	//<0 (может быть студия). Возврат прошлого результата (все по нулям)

	//добавляем в запрос еще и улицу. 
	//< 0 сделок возврат ПРОШЛОГО результата 
	//==1. точность 70%. Цена = средняя цена
	//>1. точность 80%. Цена = средняя цена
	
	//Добавляем дом.
	//< 0 сделок - возврат прошлого результата
	//==1. точность 90%. Цена = средняя цена.
	//>1. Точность 100%. цена = средняя цена
	//--------------------------------

	$sql = "select round(avg(mrk.square_meter_price)) price, count(*) as cnt from cian_general_data cg
	inner JOIN market_data mrk on left(mrk.objecttype,5)=left(cg.objecttype,5)
	WHERE cg.id={$cianId}";
	$result = mysqli_query($GLOBALS['connect'],$sql); 
	$data = mysqli_fetch_assoc($result);

	//echo "by obj.type: \r\n";
	//var_dump($data);

	if ($data['cnt'] == 0) {
		return [
			'price' => $price,
			'accuracy' => $accuracy
		];
	}
	else {
		$price = $data['price'];
		$accuracy = 50;
	}


	$sql = "select round(avg(mrk.square_meter_price)) price, count(*) as cnt from cian_general_data cg
	inner JOIN market_data mrk on left(mrk.objecttype,5)=left(cg.objecttype,5)
	WHERE cg.id={$cianId} and cg.street=mrk.street";
	$result = mysqli_query($GLOBALS['connect'],$sql); 
	$data = mysqli_fetch_assoc($result);

	//echo "by obj.street: \r\n";
	//var_dump($data);

	if ($data['cnt'] == 0) {
		return [
			'price' => $price,
			'accuracy' => $accuracy
		];
	}
	else if ($data['cnt'] == 1) {
		$price = $data['price'];
		$accuracy = 70;
	}
	else if ($data['cnt'] > 1) {
		$price = $data['price'];
		$accuracy = 80;
	}

	$sql = "select round(avg(mrk.square_meter_price)) price, count(*) as cnt from cian_general_data cg
	inner JOIN market_data mrk on left(mrk.objecttype,5)=left(cg.objecttype,5)
	WHERE cg.id={$cianId} and cg.street=mrk.street and cg.house=mrk.house";
	$result = mysqli_query($GLOBALS['connect'],$sql); 
	$data = mysqli_fetch_assoc($result);

	//echo "by obj.street + house: \r\n";
	//var_dump($data);

	if ($data['cnt'] == 0) {
		return [
			'price' => $price,
			'accuracy' => $accuracy
		];
	}
	else if ($data['cnt'] == 1) {
		$price = $data['price'];
		$accuracy = 90;
	}
	else if ($data['cnt'] > 1) {
		$price = $data['price'];
		$accuracy = 100;
	}




	return [
		'price' => $price,
		'accuracy' => $accuracy
	];
}

/* 
	Getting market data of google sheet and transfer to db
*/
function sync_market_data($service) {

	mysqli_query($GLOBALS['connect'],"START TRANSACTION");

	echo "sync market data.. \r\n";

	 //remove all data
	$sql = "DELETE FROM market_data";
	$result = mysqli_query($GLOBALS['connect'],$sql); 

	if (!$result) {
		add_logs("error clear old market data. sql: $sql");
		mysqli_query($GLOBALS['connect'],"ROLLBACK");
		return false;
	}

	//get g sheet data and push to database

	$range = 'Market!A2:J9999';
	$spreadsheetId = $GLOBALS['sheets']['general'];

	$response = $service->spreadsheets_values->get($spreadsheetId, $range);
	$values = $response->getValues();

	if (empty($values)) {
		add_logs("google sheet not contain data");
		mysqli_query($GLOBALS['connect'],"ROLLBACK");
		return false;
	}
	else {
		foreach ($values as $row) {
			//push data to db

			$data = [
				'address' => $row[2],
				'objecttype' => trim($row[3]),
				'price' => only_digit($row[4]),
				'square_meter_price' => only_digit($row[6]),
				'house' => get_address_data($row[2])['house'],
				'street' => get_address_data($row[2])['street']
			];

			$sql = "INSERT INTO market_data 
            ( 
                        id, 
                        address, 
                        objecttype, 
                        price, 
                        square_meter_price, 
                        house, 
                        street 
            ) 
            VALUES 
            ( 
                        NULL, 
                        '{$data['address']}', 
                        '{$data['objecttype']}', 
                        {$data['price']}, 
                        {$data['square_meter_price']}, 
                        '{$data['house']}', 
                        '{$data['street']}' 
            ) 
            ";

			$result = mysqli_query($GLOBALS['connect'],$sql); 

			if (!$result) {
				add_logs("error push market data to google sheet. row:" . json_encode($row) .  ". sql: $sql");
				mysqli_query($GLOBALS['connect'],"ROLLBACK");
				return false;
			}


		}
	}

	mysqli_query($GLOBALS['connect'],"COMMIT");
	return true;
}


function only_digit($value) {
	return preg_replace('/\D+/m', '', $value);
}


/*
	Return house and street using address
*/
function get_address_data($address) {

	$data = explode(',', $address);


	$house = trim($data[count($data)-1]); //last
	preg_match_all('/[\d]+/m', $house, $matches, PREG_SET_ORDER, 0);
	$house = $matches[0][0];

	if ($house == null) {
		$house = 0;
		$street = trim($data[count($data)-1]);
	}
	else {
		$street = trim($data[count($data)-2]);
	}

	

	$street = str_replace('ул.', '', $street);
	$street = str_replace('улица', '', $street);
	$street = trim($street);
	

	return [
		'street' => $street,
		'house' => $house
	];
}


/*
	Print data in google sheet
*/
function data_visualization($service) {

	echo "Push data in google sheet \n";

	 $title = 'ЦИАН Парсер.';
	 $spreadsheetId = $GLOBALS['sheets']['general'];
	 
	 //------------------
	 $requests = [
	    new Google_Service_Sheets_Request([
	        'updateSpreadsheetProperties' => [
	            'properties' => [
	                'title' => $title . ' Работает..'
	            ],
	            'fields' => 'title'
	        ]
	  ])
	];

	  // Add additional requests (operations) ...
	  $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
	      'requests' => $requests
	]);
	$response = $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
	//------------------
	sleep(1);

	//get old values. (status, auction, bonus, owners, registered, check_out, constitutive)
	$range = 'Data!A2:AI9999';

	$response = $service->spreadsheets_values->get($spreadsheetId, $range);
	$values = $response->getValues();

	//update old values
	echo "Get old values \n";
	foreach ($values as $row) {
		echo "send value id: {$row[0]} to database\n";
		$sql = "UPDATE cian_general_data cg
		SET status = \"{$row[1]}\", comments = \"{$row[2]}\", auction = \"{$row[3]}\", bonus = \"{$row[4]}\", owners = \"{$row[5]}\", registered = \"{$row[6]}\", check_out = \"{$row[7]}\", constitutive = \"{$row[8]}\"
		WHERE cg.id={$row[0]}";
		$result = mysqli_query($GLOBALS['connect'],$sql);

		if (!$result)
			add_logs("error update old value. id value: {$row[0]}. (g sheet -> database). Sql: {$sql}"); 
	}

	sleep(1); //4 полет нормальный


	//clear data
	$range = 'Data!A2:AI9999';
	$requestBody = new Google_Service_Sheets_ClearValuesRequest();
	$response = $service->spreadsheets_values->clear($spreadsheetId, $range, $requestBody);
	sleep(1); //4 полет нормальный

	$sql = "select count(id) cnt from cian_general_data";
	$result = mysqli_query($GLOBALS['connect'],$sql); 
	$cnt = mysqli_fetch_assoc($result)['cnt'];

	$OFFSET = 0; $index = 1;
	while ($OFFSET <=$cnt) {
		

		$sql = "SELECT cg.*, (select views from cian_history_data WHERE id=cg.id order by date_added desc limit 1) as views from cian_general_data cg LIMIT 50 OFFSET {$OFFSET}";
		$result = mysqli_query($GLOBALS['connect'],$sql); 

		
		while ($array = mysqli_fetch_assoc($result)) { 
			$index++;
			echo "{$index} = {$array['id']} \r\n";
			sleep(1); //4 полет нормальный


			$market_data = get_market_price($array['id']);
			//input data
			$range = "Data!A{$index}:AI{$index}";
			//!CONTROL FORMULAS.! With Adding or deleting column, column will be offset 
			$requestBody = new Google_Service_Sheets_ValueRange([
			    'values' => [
			        [	$array['id'],  //a
			        	$array['status'], //Статус. Должен лежать в базе. 
			        	$array['comments'], //Комментарии 
			        	$array['auction'], //Торг
			        	$array['bonus'], //Бонус
			        	$array['owners'], //собственников
			        	$array['registered'], // Прописанных
			        	$array['check_out'], //Выписаться
			        	$array['constitutive'], //Правоустанавливающие
			        	'=HYPERLINK("http://cian.ru/sale/flat/' . $array['id'] . '"; "Перейти")', //b
			        	$array['price_actual'], //c
			        	"=(K{$index}/M{$index})-1", //Изменение. Формула =(цена тек./цена нач)-1
			        	$array['price_start'],  //e
			        	$array['square_meter_price'], //f
			        	$market_data['price'] == null ? "Мало данных" : $market_data['price'], //Рыночная цена
			        	"=1-(O{$index}/N{$index})", //Соотношение. Формула = 1-(рын. цена мтра/цена метра)
			        	$market_data['accuracy'] == 0 ? "Мало данных" :  "{$market_data['accuracy']}%", //Точность (%)
			        	$array['views'] == null ? "Мало данных" : $array['views'], //H
			        	$array['views_all'],
			        	$array['ad_remove'] == null ? "Продается" : $array['ad_remove'], //I
			        	$array['ad_published'], //J
			        	$array['ad_added'], //
			        	$array['address'], //
			        	$array['phones'], //
			        	$array['complex_name'] == "" ? "Нет данных" : $array['complex_name'], // 
			        	$array['repair'] == "" ? "Нет данных" : $array['repair'], //
			        	$array['balconies'] == "" ? "Нет данных" : $array['balconies'], //
			        	$array['restroom'] == "" ? "Нет данных" : $array['restroom'], //
			        	$array['ceiling_height'] == "" ? "Нет данных" : $array['ceiling_height'], //
			        	$array['objecttype'],  //
			        	$array['building_year'], //
			        	$array['total_area'], //
			        	$array['floor'], //
			        	$array['description'], //
			        	$array['with_photos'] == 1 ? '=HYPERLINK("' . $GLOBALS['serv_uri'] . 'images/' . $array['id'] . '"; "Перейти")' : 'Фото удалены'

			        ] 
			    ]
			]);

			//append data
			$response = $service->spreadsheets_values->update($spreadsheetId, $range, $requestBody, ['valueInputOption' => 'USER_ENTERED']);
		}

		$OFFSET += 50;
	}

	  //LAST UPDATE
	  sleep(1);

	  mysqli_query($GLOBALS['connect'],"SET lc_time_names = 'ru_RU';"); //cnange locale

	  $sql = "select IFNULL(DATE_FORMAT(max(ad_added), '%b %e, %W'), 'Никогда') as last_update from cian_general_data";
	  $result = mysqli_query($GLOBALS['connect'],$sql); 
	  $last_update = mysqli_fetch_assoc($result)['last_update'];
	  //------------------
	  $requests = [new Google_Service_Sheets_Request([
	        'updateSpreadsheetProperties' => [
	            'properties' => [
	                'title' => $title . ' Поседнее обновление базы: ' . $last_update
	            ],
	            'fields' => 'title'
	        ]
	    ])
	  ];

	  // Add additional requests (operations) ...
	  $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
	      'requests' => $requests
	  ]);
	  $response = $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);




}

/*
	Verifying status ads in database. If ad will be removed => update row
*/
function get_removed_status() {

	echo "Verifying status ads (is removed or no) \r\n";
	 //check only ads, which have status publish
	$sql = "SELECT count(id) cnt from cian_general_data where ad_remove is null";
	$result = mysqli_query($GLOBALS['connect'],$sql); 
	$cnt = mysqli_fetch_assoc($result)['cnt'];

	$OFFSET = 0; $index = 1;

	while ($OFFSET <= $cnt) {
		$sql = "SELECT id from cian_general_data where ad_remove is null LIMIT 50 OFFSET {$OFFSET}";
		$result = mysqli_query($GLOBALS['connect'],$sql); 

		while ($array = mysqli_fetch_assoc($result)) {
			//sleep(1); //на всякий
	        
	        echo "{$index} - checking remove status ad: {$array['id']} \r\n";

	        // if (info_ad($array['id'], $GLOBALS['endpoints']['page']) == null) {
	        // 	//ads has been removed. change status
	        // 	echo "found unactive ad: {$array['id']} \r\n";
	        // 	$sql = "update cian_general_data set ad_remove=curdate() WHERE id={$array['id']}";
	        // 	$result = mysqli_query($GLOBALS['connect'],$sql); 
	        // }
	        // else {
	        // 	echo "ad: {$array['id']} is active\r\n";
	        // }

	        $index++;


	    }

	    $OFFSET += 50;
	}
		

}

/*
	Function write all data, using ads ids, in database.
	if transction not be сompleted, returned false. else true

*/
function push_data_to_db($ads_ids) {

	//create temporary table
	$sql = "CREATE TABLE IF NOT EXISTS temp_data SELECT * FROM cian_general_data LIMIT 0";
	$result = mysqli_query($GLOBALS['connect'],$sql); 

	mysqli_query($GLOBALS['connect'],"ALTER TABLE temp_data ADD PRIMARY KEY (ID);"); //add primary key

	if (!$result) {
		
		add_logs("error create temp table. Sql: {$sql}");
		return false;
	}
	else {
		//temporary table created

		foreach ($ads_ids as $key) {
			echo "id : {$key} \r\n";
			$attr = info_ad($key, $GLOBALS['endpoints']['page']);

			if ($attr == null) {
				add_logs("ad {$key} not have attributes. skip");
				continue; //skip
			}


			$attr_c = $attr['constant'];
			$attr_v = $attr['variable'];


			//push all data in temp table
			$sql = "INSERT INTO temp_data 
	            ( 
	                        id, 
	                        description,
				            status,
				            auction,
				            bonus,
				            owners,
				            registered,
				            check_out,
				            constitutive,
				            comments,
	                        price_actual, 
	                        price_start, 
	                        address, 
	                        complex_name, 
	                        repair, 
	                        balconies, 
	                        restroom, 
	                        ceiling_height, 
	                        floor, 
	                        objecttype, 
	                        building_year, 
	                        ad_published, 
	                        total_area,
	                        square_meter_price,
	                        phones,
	                        views_all,
	                        ad_added,
	                        ad_remove,
	                        street,
	                        house,
	                        with_photos
	            ) 
	            VALUES 
	            ( 
	                        {$attr_c['id']}, 
	                        \"{$attr_c['description']}\", 
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        {$attr_v['price_actual']}, 
	                        {$attr_v['price_start']}, 
	                        '{$attr_c['address']}', 
	                        '{$attr_c['complex_name']}', 
	                        '{$attr_c['repair']}', 
	                        '{$attr_c['balconies']}', 
	                        '{$attr_c['restroom']}', 
	                        '{$attr_c['ceiling_height']}', 
	                        '{$attr_c['floor']}', 
	                        '{$attr_c['objectType']}', 
	                        '{$attr_c['building_year']}', 
	                        '{$attr_c['ad_published']}', 
	                        '{$attr_c['total_area']}',
	                        '{$attr_v['square_meter_price']}',
	                        '{$attr_v['phones']}',
	                        {$attr_v['views_all']},
	                        NOW(),
	                        {$attr_v['ad_remove']},
	                        '{$attr_c['street']}',
	                        '{$attr_c['house']}',
	                        1
	            )";

			$result = mysqli_query($GLOBALS['connect'],$sql); 

			if (!$result)
				add_logs("error added ad {$key} in temp table. Sql: {$sql}");

			//load images, if need
			$sql = "select count(id) cnt FROM cian_general_data where id={$key}";
			$result = mysqli_query($GLOBALS['connect'],$sql); 
			$cnt = mysqli_fetch_assoc($result)['cnt'];
			if ($cnt == 0) {
				//load images 

				$path = "images/{$key}/";
				mkdir($path);

				for ($i=0;$i<count($attr_c['photos']); $i++) { 

					if ($attr_c['photos'][$i]['type'] == 'photo' || $attr_c['photos'][$i]['type'] == 'layout') {
						$path = "images/{$key}/{$i}.jpg";
						$photo = file_get_contents($attr_c['photos'][$i]['previewUrl']);
						file_put_contents($path, $photo);
					}
				}

			}



		}

		//Транзакция --работа с старыми данным
		mysqli_query($GLOBALS['connect'],"START TRANSACTION");


		//1. Удаляем все записи в истории старых объявлений если уже было что - то записано в этот день
		//Иными словами если парсер был запущен в этот день - данные будут перезаписаны
		$sql = "DELETE FROM cian_history_data 
		WHERE  id IN(SELECT tmp.id 
		             FROM   temp_data tmp 
		                    INNER JOIN cian_general_data cg 
		                            ON cg.id = tmp.id) 
		       AND date_added = Curdate()";
		$result = mysqli_query($GLOBALS['connect'],$sql); 

		if (!$result) {
			add_logs("error delete cian history data. ROLLBACK. sql: $sql");
			mysqli_query($GLOBALS['connect'],"ROLLBACK");
			return false;
		}

		//2. Все что в основной таблице - пушим в историю
		$sql ="INSERT INTO cian_history_data 
	            (id, 
	             date_added, 
	             price, 
	             square_meter_price, 
	             phones, 
	             views) 
		SELECT cg.id, 
		       Curdate()                    AS date_added, 
		       cg.price_actual              AS price, 
		       cg.square_meter_price, 
		       cg.phones, 
		       tmp.views_all - cg.views_all AS views 
		FROM   temp_data tmp 
		       INNER JOIN cian_general_data cg 
		               ON cg.id = tmp.id";

		$result = mysqli_query($GLOBALS['connect'],$sql); 

		if (!$result) {
			add_logs("error inserting in history default data (cian_general_data --> cian_history_data). ROLLBACK. sql: $sql");
			mysqli_query($GLOBALS['connect'],"ROLLBACK");
			return false;
		}
		

		//3. Изменяем основные данные из временной таблицы
		$sql = "UPDATE cian_general_data cg 
	       INNER JOIN temp_data tmp 
	               ON cg.id = tmp.id 
		SET    cg.price_actual = tmp.price_actual, 
	       cg.square_meter_price = tmp.square_meter_price, 
	       cg.phones = tmp.phones, 
	       cg.views_all = tmp.views_all, 
	       cg.ad_remove = tmp.ad_remove";
	    $result = mysqli_query($GLOBALS['connect'],$sql); 

	    if (!$result) {
			add_logs("error updating default data with temp table (temp_data--> cian_default_data). ROLLBACK. sql: $sql");
			mysqli_query($GLOBALS['connect'],"ROLLBACK");
			return false;
		}

		mysqli_query($GLOBALS['connect'],"COMMIT");
	    //Транзакция --работа с старыми данным

	    //4. Добавление новых объявлений из временной таблицы (ранее не было такого объявления)

	    $sql = "INSERT INTO cian_general_data 
	            (id, 
	             description,
	             status,
	             auction,
	             bonus,
	             owners,
	             registered,
	             check_out,
	             constitutive,
	             comments,
	             price_actual, 
	             price_start, 
	             address, 
	             street,
	             house,
	             square_meter_price, 
	             phones, 
	             complex_name, 
	             repair, 
	             balconies, 
	             restroom, 
	             ceiling_height, 
	             total_area, 
	             floor, 
	             objecttype, 
	             building_year, 
	             views_all, 
	             ad_published, 
	             ad_added, 
	             ad_remove,
	             with_photos) 
		SELECT tmp.* 
		FROM   temp_data tmp 
		       LEFT JOIN cian_general_data cg 
		              ON cg.id = tmp.id 
		WHERE  cg.id IS NULL";


		$result = mysqli_query($GLOBALS['connect'],$sql); 

	    if (!$result) {
			add_logs("error adding new ads using temp table (temp_data --> cian_general_data). sql: $sql");
			return false;
		}

		//----check old photos and delete--------

		//Если объявление больше полугода в статусе неактивном находится - удалить фотки.
		//Получить айди и удалить фото
		//
		//и возможно флаг добавить. Фото есть/нет.
		//И изменять потом в бд что фотки нет. Но пока это не нужно, повременим.
		//Нужно пагинацию добавить еще.
		//UPDATE cian_general_data
		//SET with_photos=0

		$sql = "select count(id) cnt from cian_general_data cg WHERE DATEDIFF(NOW(), cg.ad_remove) > 180";
		$result = mysqli_query($GLOBALS['connect'],$sql); 
		$cnt = mysqli_fetch_assoc($result)['cnt'];

		$OFFSET = 0; $index = 1;


		while ($OFFSET <=$cnt) {
			

			$sql = "select id from cian_general_data cg WHERE DATEDIFF(NOW(), cg.ad_remove) > 180 LIMIT 50 OFFSET {$OFFSET}";
			$result = mysqli_query($GLOBALS['connect'],$sql); 

			
			while ($array = mysqli_fetch_assoc($result)) { 
				$index++;
				$id = $array['id'];

				echo "found old photos: {$id}. Deleting..\n";

				//remove photos
				$path = "images/{$id}/";
				array_map('unlink', glob("$path/*.*"));
				rmdir($path);

				mysqli_query($GLOBALS['connect'], "UPDATE cian_general_data SET with_photos=0 WHERE id={$id}"); //mark as removed
			}

			$OFFSET += 50;

		}
		//----check old photos and delete--------

		$sql = "DROP TABLE temp_data";
		$result = mysqli_query($GLOBALS['connect'],$sql); 

	    return true;

	}


}



/*
 return list attributes using ad_id 
*/
function info_ad($id, $endpoint) {

	echo "getting info by ad id: " . $id . "\r\n";

	try {

		$page = json_decode(file_get_contents(sprintf($endpoint, $id) ), true);

		if (isset($page['errors'])) {
			//Возможно снято с публикации. Но скорее всего нет.
			add_logs("get error with page: {$id}. body: $page");
			return null;
		}
		else {
			//Можно парсить аттрибуты
			if ($page['offer']['status'] != "published") {
				//Снято с публикации
				add_logs("ad {$id} has been changed status.New status: {$page['offer']['status']}");
				return null;
			}


		}

		$attributes = [

			/* parsing only first time */
			"constant" => [
				'id' => $id,
				'description' => implode(' ', $page['offer']['description']), //строка
				'address' =>  get_adress($page['offer']['geo']['address']), //строка
				

				/* This attributes list can be not exist */
				'complex_name' => isset($page['offer']['building']['features']['name']) ? $page['offer']['building']['features']['name'] : null, //название ЖК строка
				'repair' => isset($page['offer']['general']['repair']) ? $page['offer']['general']['repair'] : null, //ремонт строка
				'balconies' => isset($page['offer']['general']['balconies']) ? $page['offer']['general']['balconies'] : null, //балкон строка
				'restroom' => isset($page['offer']['general']['restroom']) ? $page['offer']['general']['restroom'] : null, //Санузел строка
				'ceiling_height' => isset($page['offer']['general']['ceilingHeight']) ? $page['offer']['general']['ceilingHeight'] : null, //число дробное
				/* This attributes list can be not exist */	
				
				'floor' => $page['offer']['features']['floorInfo'], //Пока храним как строку
				'objectType' => $page['offer']['features']['objectType'], //Однокмнатная двукомнатная и тд
				
				'ad_published' => get_date($page['offer']['meta']['addedAt']), //Храним как строку
				'total_area' => explode(' ', $page['offer']['features']['totalArea'])[0], //число
				'photos' => $page['offer']['media']
			],
			"variable" => [
				'price_actual' => $page['offer']['price']['value'], //число
				'price_start' => $page['offer']['price']['value'], //число
				'square_meter_price' => only_digit($page['offer']['additionalPrice']), //строка.
				'phones' => implode(', ', $page['offer']['phones']), //строка
				'views_all' => $page['offer']['meta']['views'], //число
				'ad_remove' => "null"
				//'ad_type' => -1, //будет строка платное, премиум, топ	.while not using
			]
			
			
					
		];

		$attributes['constant']['description'] =  preg_replace('/["\']/m', '', $attributes['constant']['description']);
		$attributes['variable']['phones'] = preg_replace('/[+][7]/m', '', $attributes['variable']['phones']); //replace +
		
		$attributes['constant']['total_area'] = preg_replace('/[?\sм²]+/m', '', $attributes['constant']['total_area']); //replace m2
		$attributes['constant']['total_area'] = trim($attributes['constant']['total_area']);

		//if exist history. set start price and date publish from history
		if (isset($page['priceChange']))
			if (count($page['priceChange']) > 0) {
				//cnage price and date publicaiton
				$attributes['constant']['ad_published'] = $page['priceChange'][0]['date'];
				$attributes['variable']['price_start'] = $page['priceChange'][0]['price'];
			}

		if ($page['offer']['price']['currency'] != "rur") {
			//change all prices

			$attributes['variable']['price_start'] = only_digit($page['offer']['rublesPrice']);
			$attributes['variable']['price_actual'] = only_digit($page['offer']['rublesPrice']);

			$attributes['variable']['square_meter_price'] = intdiv($attributes['variable']['price_actual'],$attributes['constant']['total_area']); //recalc square_meter_price

		}



		$attributes['constant']['street'] =  get_address_data($attributes['constant']['address'])['street'];
		$attributes['constant']['house'] =  get_address_data($attributes['constant']['address'])['house'];


		if (isset($page['offer']['houseInfo']['info']['buildYear'])) {
			$attributes['constant']['building_year'] = "построен: " . $page['offer']['houseInfo']['info']['buildYear'];
		}
		else if (isset($page['offer']['building']['features']['deadline'])) {
			$attributes['constant']['building_year'] = "срок сдачи: " . $page['offer']['building']['features']['deadline'];
		}
		else {
			$attributes['constant']['building_year'] = 'нет данных';
		}

		//Где тот тут проверить опубликовано ли
	}
	catch (Exception $e) {
		add_logs("error: " . $e->getMessage());
		add_logs("body json (page): " . json_encode($page));
		add_logs("ad id :" . $id);
	}

	return $attributes;

}


/*
	return list ids using search parametrs
 */
function get_ads_ids($find_data, $endpoint) {

	$page = 0;
	$ads_pages_cnt = 0;
	$ads_ids = []; //list CianId
	$ads_cnt = 0;

	/*get ads ids*/
	while ($page <= $ads_pages_cnt) {

		try {

			$page++;

			change_page($find_data, $page);
			$offers = send_post($endpoint, $find_data, ["Content-Type: application/json"]);

			//echo "offers: ";
			//echo "$offers";

			/* calculate only first time */
			$pages = json_decode($offers, true);
			if ($page == 1) {				
				$ads_cnt = $pages['aggregatedOffersCount']; //get count all ads
				$ads_pages_cnt = $ads_cnt / count($pages['offers']); //get count pages
				$ads_pages_cnt = (int)$ads_pages_cnt;

				if ( ($ads_cnt%$ads_pages_cnt) > 0)
					$ads_pages_cnt++; //if how minimum one element left, exist also one page
			}

			echo "all offers found: " . $ads_cnt . "\r\n";
			echo "count pages: " . $ads_pages_cnt . "\r\n";
			echo "current page: " . $page . "\r\n";

			echo "\r\n--Offers--\r\n";

			for ($i=0; $i < count($pages['offers']); $i++) { 

				echo $pages['offers'][$i]['cianId'] . "\r\n";
				$ads_ids[] = $pages['offers'][$i]['cianId'];
			}

		}
		catch (Exception $e) {
			add_logs("parsing id list: " . $e->getMessage());
			add_logs("body json (offers): " . $offers);
			add_logs("page: " . $page . ". pages count: ". $ads_pages_cnt . ". ads count: " . $ads_cnt);
		}
		

	}



	echo "ads count: " . $ads_cnt . " cian id cnt: " . count($ads_ids) . "\r\n";

	/* get attributes for each ad */
	if (count($ads_ids) - $ads_cnt >= 0) {
		//all so ok
	}
	else {
		add_logs("didn't match cnt ads (ads_ids: {$ads_ids} vs. ads_cnt {$ad_cnt}), ads pages count: {$ads_pages_cnt}");
		add_logs("ads_ids list: " . json_encode($ads_ids));
	}

	return $ads_ids;

}

function change_page(&$find_data, $new_page) {

	$array = json_decode($GLOBALS['find_data'], true);
	$array['jsonQuery']['page']['value'] = $new_page;

	$find_data = json_encode($array);
}

function get_adress($array) {

	$full_adress = $array[0]['value'];

	for ($i=1; $i < count($array); $i++) { 

		$key = $array[$i]['key'];
		$value =  $array[$i]['value'];
		

		switch ($key) {
			case 'location':
			case 'street':
				$full_adress .= ', ' . $value;
				break;
			case 'house':
				$full_adress .= ', д. '. $value;
				break;			
			default:
				$full_adress .= ', ' . $value;
				break;
		}
	}

	return $full_adress;
}

function get_date($string_date) {
	
	$day = explode(' ', $string_date)[0];
	$date = null; //formated date

	switch (mb_strtolower($day)) {
		case 'сегодня':
			$date = date("d F");
			break;
		case 'вчера':
			$date = date("d F",strtotime("-1 day"));
			break;
		default:
			$date = $string_date;
			break;
	}

	return $date;
}

?>