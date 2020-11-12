<?php

/*
	Работа с бд тут.
 */
namespace Parser;

include_once __DIR__ . '/logs.php';
include_once __DIR__ . '/data.php';

class Database
{

	private $connect; //строка с нашим соеденением
	private $logs; //Экземпляр для логов
	private $data; //Экземляр для данных

	function __construct($config, $path_logs) {

		$this->connect = mysqli_connect(
			$config['servername'], 
			$config['username'], 
			$config['password'], 
			$config['database']
		);

		if (!$this->connect)
			throw new \Exception("Connection Failed: " . mysqli_connect_error());

		mysqli_query($this->connect, "SET NAMES 'utf8'"); 
		mysqli_query($this->connect, "SET CHARACTER SET 'utf8'");
		mysqli_query($this->connect, "SET SESSION collation_connection = 'utf8_general_ci'");
		mysqli_query($this->connect, 'SET SESSION wait_timeout=86400'); 

		mysqli_query($this->connect, "SET lc_time_names = 'ru_RU';"); //cnange locale

		ini_set('mysqli.reconnect', 1);
		$this->logs = new \Parser\Logs($path_logs, "Database");

		$this->data = new \Parser\Data();

	}

	/**
	 * Возвращает первую запись из выборки данных используя SQL
	 * @param string $sql
	 * @return Array || false
	*/
	function getFirst($sql) {

		$result = mysqli_query($this->connect,$sql); 

		if (!$result)
			return false;

		return mysqli_fetch_assoc($result);
	}


	/**
	 * Возвращает выборку данных, учитывает пагинацию. Используя SQL запрос
	 * @param string $sql
	 * @return Array || false
	*/
	function getAll($sql) {

		$per_page = 50;

		$result = mysqli_query($this->connect,$sql . " limit {$per_page} offset 0"); 

		if (!$result)
			return false;

		$data = []; //Что будет на выходе

		$offset = 0;
		while (true) {
			
			$result = mysqli_query($this->connect,$sql . " limit {$per_page} offset {$offset}"); 

			if ($result->num_rows == 0)
				break; //Данных больше нет
			
			while ($array = mysqli_fetch_assoc($result))
				$data[] = $array;

			$offset += 50;
		}

		return $data;
	}


	/**
	 * Выполняет sql запрос. Возвращает true || false. например будет полезен для обновления/удаления данных
	 * @param string $sql
	 * @return bool
	*/
	function request($sql)
	{
		$result = mysqli_query($this->connect,$sql); 

		if (!$result)
			return false;

		echo "affected row(s): " . mysqli_affected_rows($this->connect) . "\n";

		return true;
	}



	/**
	 * Обновляет все существующие объявления в бд и добавляет новые объявления в базу
	 * @param Array $data
	 * @param string $source
	 * @return Array (новые объявления) || false
	*/
	function update_ads($data, $source) {

		$id_source = $this->getFirst("select id from sources where name='{$source}'")['id'];
		if ($id_source == null)
			return false; //source not supported

		$this->request("START TRANSACTION");

		//Создаем временную таблицу на основе нашей основной
		$status = $this->request("CREATE TEMPORARY TABLE IF NOT EXISTS temp_data SELECT * FROM general_data LIMIT 0");
		if (!$status) {
			$this->request("ROLLBACK");
			$this->logs->addMessage("error create temporary table for source {$source}. reason: " . mysqli_error($this->connect)); 
			return false;
		}
		$status = $this->request("ALTER TABLE temp_data ADD PRIMARY KEY (id, id_source);"); //add primary key
		if (!$status) {
			$this->request("ROLLBACK");
			$this->logs->addMessage("error create temporary table for source {$source}. reason: " . mysqli_error($this->connect)); 
			return false;
		}

	    //Создаем еще одну временную таблицу для фото
	    $status = $this->request("create temporary table images_temp(
			id int,
			id_source int,
			links varchar(7000),
			primary key (id, id_source)
		)"); //7000 = до 50 ссылок, до 140 символов на ссылку

	    if (!$status) {
	    	$this->request("ROLLBACK");
			$this->logs->addMessage("error create temporary table images_temp for source {$source}. reason: " . mysqli_error($this->connect)); 
			return false;
	    }
		

		foreach ($data as $item) {

			$attr = $item['attributes'];  //Наши аттрибуты
			$photos = implode(',', $attr['photos']); //Конвертируем в строку, так как нужно положить в базу
			$status = $this->request("insert into images_temp (id,id_source,links) values({$attr['id']},{$id_source},\"{$photos}\")");
			if (!$status)
	            	//записать в логи. Транзакцию откатывать не нужно
	            	$this->logs->addMessage("error added pictures for ad {$attr['id']} in images_temp. Reason: " . mysqli_error($this->connect));	


			$status = $this->request("INSERT INTO temp_data 
	            ( 
	                        id, 
	                        id_source,
	                        description,
	                        link,
				            status,
				            auction,
				            bonus,
				            owners,
				            registered,
				            check_out,
				            constitutive,
				            price_sale,
				            target_profit,
				            comments,
	                        price_actual, 
	                        price_start, 
	                        address, 
	                        complex_name, 
	                        repair, 
	                        balconies, 
	                        restroom, 
	                        ceiling_height,
	                        current_floor,
	                        total_floors,
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
	                        {$attr['id']}, 
	                        {$id_source},
	                        \"{$attr['description']}\", 
	                        '{$attr['link']}',
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        '',
	                        {$attr['price_actual']}, 
	                        {$attr['price_start']}, 
	                        '{$attr['address']}', 
	                        '{$attr['complex_name']}', 
	                        '{$attr['repair']}', 
	                        '{$attr['balconies']}', 
	                        '{$attr['restroom']}', 
	                        '{$attr['ceiling_height']}', 
	                        '{$attr['current_floor']}', 
	                        '{$attr['total_floors']}',  
	                        '{$attr['objectType']}', 
	                        '{$attr['building_year']}', 
	                        '{$attr['ad_published']}', 
	                        '{$attr['total_area']}',
	                        '{$attr['square_meter_price']}',
	                        '{$attr['phones']}',
	                        {$attr['views_all']},
	                        NOW(),
	                        {$attr['ad_remove']},
	                        '{$attr['street']}',
	                        '{$attr['house']}',
	                        1
	            )");

	            if (!$status)
	            	//записать в логи. Транзакцию откатывать не нужно
	            	$this->logs->addMessage("error added ad {$attr['id']} in temp table. Data: " . json_encode($attr) . ". Reason: " . mysqli_error($this->connect));	

		}

		//1. Удаляем все записи в истории старых объявлений если уже было что - то записано в этот день
		//Иными словами если парсер был запущен в этот день - данные будут перезаписаны
		//*записи удаляются только этого источника

		$status = $this->request("DELETE hd FROM history_data hd
		INNER JOIN general_data gd 
				ON (gd.id,gd.id_source)=(hd.id_ad,hd.id_source)
		INNER JOIN temp_data tmp
				ON (tmp.id,tmp.id_source)=(hd.id_ad,gd.id_source)
		WHERE gd.id_source={$id_source}
		AND hd.date_added=Curdate()");

		if (!$status) {
			$this->request("ROLLBACK");
			$this->logs->addMessage("error delete history data of today for source {$source}. reason: " . mysqli_error($this->connect)); 
			return false;
		}

		//2. Пишем в историю все что в основной таблице 
		//*только существующие объявления
		$status = $this->request("INSERT INTO history_data 
	            (id,
	             id_ad, 
	             id_source,
	             date_added, 
	             price, 
	             square_meter_price, 
	             phones, 
	             views) 
		SELECT null, 
			   gd.id, 
			   gd.id_source,
		       Curdate()                    AS date_added, 
		       gd.price_actual              AS price, 
		       gd.square_meter_price, 
		       gd.phones, 
		       tmp.views_all - gd.views_all AS views 
		FROM   temp_data tmp 
		       INNER JOIN general_data gd 
		               ON (gd.id, gd.id_source) = (tmp.id, tmp.id_source)");

		if (!$status) {
			$this->request("ROLLBACK");
			$this->logs->addMessage("error moving current data to history for source {$source}. reason: " . mysqli_error($this->connect)); 
			return false;
		}

		//3. Обновляем данные на актуальные используя данные из временной таблицы
		$status = $this->request("UPDATE general_data gd 
	       INNER JOIN temp_data tmp 
	              ON (gd.id, gd.id_source) = (tmp.id, tmp.id_source)
		SET gd.price_actual = tmp.price_actual, 
	       gd.square_meter_price = tmp.square_meter_price, 
	       gd.phones = tmp.phones, 
	       gd.views_all = tmp.views_all, 
	       gd.ad_remove = tmp.ad_remove");

		if (!$status) {
			$this->request("ROLLBACK");
			$this->logs->addMessage("error update general data from temp data for source {$source}. reason: " . mysqli_error($this->connect)); 
			return false;
		}


		//Производим выборку только новых объявлений
		$new_data = $this->getAll("SELECT tmp.id, '{$source}' source, img_tmp.links 
		FROM   temp_data tmp 
		       LEFT JOIN general_data gd 
		              ON (gd.id, gd.id_source) = (tmp.id, tmp.id_source)
		       INNER JOIN images_temp img_tmp 
                      ON (tmp.id, tmp.id_source) = (img_tmp.id, img_tmp.id_source)
        WHERE  gd.id IS NULL  and gd.id_source IS NULL
		");

		//4. Добавление новых объявлений из временной таблицы (ранее не было такого объявления)
		$status = $this->request("INSERT INTO general_data 
	            (id, 
	             id_source,
	             description,
	             link,
	             status,
	             auction,
	             bonus,
	             owners,
	             registered,
	             check_out,
	             constitutive,
	             price_sale,
				 target_profit,
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
	             current_floor,
	             total_floors, 
	             objecttype, 
	             building_year, 
	             views_all, 
	             ad_published, 
	             ad_added, 
	             ad_remove,
	             with_photos) 
		SELECT tmp.* 
		FROM   temp_data tmp 
		       LEFT JOIN general_data gd 
		              ON (gd.id, gd.id_source) = (tmp.id, tmp.id_source)
		WHERE  gd.id IS NULL  and gd.id_source IS NULL");

		if (!$status) {
			$this->request("ROLLBACK");
			$this->logs->addMessage("error inserting new data for source {$source}. reason: " . mysqli_error($this->connect)); 
			return false;
		}


		//Дропаем временные таблицы
		$status = $this->request("DROP TEMPORARY TABLE temp_data");
		if (!$status)
			$this->logs->addMessage("error drop temporary table temp_data for source {$source}. reason: " . mysqli_error($this->connect));
		
		$status = $this->request("DROP TEMPORARY TABLE images_temp");
		if (!$status)
			$this->logs->addMessage("error drop temporary table images_temp for source {$source}. reason: " . mysqli_error($this->connect));

		//Ошибок нет если добрались до сюда 
		$this->request("COMMIT");

		//Преобразуем назад изображения в массив
		foreach ($new_data as $i => $item) {
			$new_data[$i]['photos'] = explode(',', $item['links']);
			unset($new_data[$i]['links']);
		}
		

		return $new_data; //Возврат массива только из новых айди

	}

	/**
	 * Возвращает все объявления где фото уже можно удалять.
	 * @param Array $data
	 * @param string $source
	 * @return Array (новые объявления) || false
	*/
	function getOldPhotos($days=180) {
		$result = $this->getAll("select gd.id, so.name source from general_data gd
		inner join sources so on so.id=gd.id_source
		where DATEDIFF(NOW(), gd.ad_remove) > {$days}");

		if ($result === false)
			$this->logs->addMessage("error getting ads with old photos. days: {$days}. reason: " . mysqli_error($this->connect));

		return $result;

	}

	/**
	 * Помечает переданный список объявлений как без фото
	 * формат объекта {id, source}
	 * @param Array $data
	*/
	function withoutPhotos($data) {

		foreach ($data as $ad) {

			$status = $this->request("UPDATE general_data gd
			inner join sources so on so.id=gd.id_source
			set gd.with_photos=0
			where gd.id={$ad['id']} and so.name='{$ad['source']}'");

			if (!$status)
				$this->logs->addMessage("error mark photo ({$ad['id']},{$ad['source']}') as removed. reason: " . mysqli_error($this->connect));
		}

	}

	/**
	 * изменяет статус у списка объявлений
	 * Формат объекта {id, source, is_active}
	 * @param Array $data
	*/
	function changeStatus($data) {

		foreach ($data as $ad) {
			//Формируем условие в зависимости от статуса у объекта
			$new_status = $ad['is_active'] == true ? "null" : "curdate()";

			$where = ''; //По умолчанию не добавляем ничего
			
			//Если новый статус объявления неактивен 
			//перезаписать только если объявление на продаже (gd.ad_remove is null)
			if ($ad['is_active'] == false)
				$where = ' and gd.ad_remove is null';

			//Стаутс объяления != null (не продается) пытаемся установить статус активно - устанавливается
			//статус объявления != null пытаемся установить статус неактивно - остается прошлая дата 
			//статус объявления == null, (продается) пытаемся установить стаус активно - остается null
			//статус объявления == null - пытаемся установить стаус неактивно - ставится текущая дата
			
			
			$status = $this->request("UPDATE general_data gd
			inner join sources so on so.id=gd.id_source
			set ad_remove={$new_status}
			where gd.id={$ad['id']} and so.name='{$ad['source']}' {$where}"); 

			if (!$status)
				$this->logs->addMessage("error change status ad ({$ad['id']},{$ad['source']})");

		}

	}

	/**
	 * Получаем все объявления согласно условиям.
	 * {
	 *   fields:  gd.*, so.hint, * (перечисляем список полей)
	 * 	 is_active: [true,false,null] (opt) - активно ли
	 * 	 source=<source_name> or null (ограничение по источнику или все источники)
	 *   custom_joins: for example inner join id_sheet id_s on id_s.id=cg.id (доп. связи)
	 *  }
	 * @param Array $conditions
	*/
	function getAds($conditions) {

		//Перечислим параметры по умолчанию и значения для них
		$optional_parametrs = [
			"custom_joins" => "",
			"is_active" => null,
			"source" => null,
			"id" => null 
		];

		//Если какой то параметр не был передан - установим его самостоятельно
		foreach ($optional_parametrs as $key => $default_value) {
			if (!isset($conditions[$key]))
				$conditions[$key] = $default_value;
		}
		

		$where = []; //массив где будут все условия

		//Статус - активно или нет
		switch ($conditions['is_active']) {
			case null:
				//nothing added.
				break;
			case true:
				$where[] = "gd.ad_remove is not null";
				break;
			case false:
				$where[] = "gd.ad_remove is null";
				break;
			
		}

		//Источник. 
		switch ($conditions['source']) {
			case null:
				//nothing added.
				break;
			
			default:
				//adding condition source
				$where[] = "so.name ='{$conditions['source']}'";
				break;
		}

		//Ограничение по (id)
		switch ($conditions['id']) {
			case null:
				//nothing added.
				break;
			
			default:
				$where[] = "gd.id={$conditions['id']}";
				break;
		}


		$sql = "
		select {$conditions['fields']},
		(select views from history_data hd WHERE (gd.id,gd.id_source)=(hd.id_ad,hd.id_source) order by date_added desc limit 1) as views 
		from general_data gd
		inner join sources so on so.id=gd.id_source
		{$conditions['custom_joins']} 
		";

		if (count($where) == 1) //need only where
			$sql .= " where " . implode(" ", $where);
		else if (count($where) > 1)
			$sql .= " where " . implode(" and ", $where); //need where  + and

		return $this->getAll($sql);


	}


	/**
	 * Расчет рыночной цены
	 * @param int $id
	 * @param int $id_source
	*/
	function getMarketPrice($id, $id_source) {
		$price = 0;
		$cnt = 0;
		$accuracy = 0;

		//--------------------------------
		//Точность зависит от кол-ва транзакций и как далеко мы уточнили критерии поиска (до дома квартиры и тд)

		//ищем все квартиры которые входят в группу по этажности как и у нас.
		//Разделяем на 2 группы (квартиры которые имеют до 5 этажей (включительно) и от 6 и выше)

		//ищем тип квартиры такой же как и у нашей. выоводим ср. цену рыночную и кол-во сделок
		//точность = 50%
		//цена = средняя цена
		//<0 (может быть студия). Возврат прошлого результата (все по нулям)

		//Добавляем еще этажность
		//<0 - возврат прошлого результата
		//все остальные варианты. точность = 50.

		//добавляем в запрос еще и улицу. 
		//< 0 сделок возврат ПРОШЛОГО результата 
		//==1. точность 70%. Цена = средняя цена
		//>1. точность 80%. Цена = средняя цена
		
		//Добавляем дом.
		//< 0 сделок - возврат прошлого результата
		//==1. точность 90%. Цена = средняя цена.
		//>1. Точность 100%. цена = средняя цена
		//--------------------------------

		//Получить этажность текущего объекта.
		//Текущая >=6 => where >=6
		//Текущая <=5 => where <=5
		$total_floors = $this->getFirst("select total_floors from general_data where (id,id_source)=({$id},{$id_source})")['total_floors'];

		if ($total_floors >= 6)
			$floor_condition = ">=6";
		else 
			$floor_condition = "<=5";

		//Тип объекта только смотрим. 
		$data = $this->getFirst("select round(avg(mrk.square_meter_price)) price, count(*) as cnt from general_data gd
		inner JOIN market_data mrk on left(mrk.objecttype,5)=left(gd.objecttype,5)
		where (gd.id,gd.id_source)=({$id},{$id_source})");
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
			$accuracy = 0;
		}

		//Добавляем этажность
		$data = $this->getFirst("select round(avg(mrk.square_meter_price)) price, count(*) as cnt from general_data gd
		inner JOIN market_data mrk on left(mrk.objecttype,5)=left(gd.objecttype,5)
		where (gd.id,gd.id_source)=({$id},{$id_source}) and mrk.total_floors{$floor_condition}");

		if ($data['cnt'] == 0) {
			return [
				'price' => $price,
				'accuracy' => $accuracy
			];
		}
		else {
			$price = $data['price'];
			$accuracy = 50; //Интересует только больше 1 сделки
		}

		//Добавляем улицу
		$data = $this->getFirst("select round(avg(mrk.square_meter_price)) price, count(*) as cnt from general_data gd
		inner JOIN market_data mrk on left(mrk.objecttype,5)=left(gd.objecttype,5)
		where (gd.id,gd.id_source)=({$id},{$id_source}) and gd.street=mrk.street and mrk.total_floors{$floor_condition}");

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

		//Добавляем дом
		$data = $this->getFirst("select round(avg(mrk.square_meter_price)) price, count(*) as cnt from general_data gd
		inner JOIN market_data mrk on left(mrk.objecttype,5)=left(gd.objecttype,5)
		where (gd.id,gd.id_source)=({$id},{$id_source}) and gd.street=mrk.street and gd.house=mrk.house and mrk.total_floors{$floor_condition}");

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


	/**
	 * Обновляет данные по рыночным ценам исходя из таблицы в гугле
	 * @param Array $sheetValues (выборка из geValues)
	 * @param Array $ranges (можно взять из настроек $settings->sheet_ranges)
	 * @return bool
	*/
	function sync_market_data($sheetValues) {

		$this->request("START TRANSACTION");

		echo "sync market data.. \r\n";

		 //remove all data
		$status = $this->request("DELETE FROM market_data");

		if (!$status) {
			$this->logs->addMessage("error clear old data in table: market_data. reason: " . mysqli_error($this->connect));
			$this->request("ROLLBACK");
			return false;
		}

		//get g sheet data and push to database

		if (empty($sheetValues)) {
			$this->logs->addMessage("sync_market_data: range not contain data");
			$this->request("ROLLBACK");
			return false;
		}
		else {
			foreach ($sheetValues as $row) {
				//push data to db

				$address_data = $this->data->decomposeAddress($row[2]);

				$data = [
					'address' => $row[2],
					'objecttype' => trim($row[3]),
					'price' => $this->data->getDigit($row[4]),
					'square_meter_price' => $this->data->getDigit($row[6]),
					'house' => $address_data['house'],
					'street' => $address_data['street'],
					'current_floor' => (int)$row[11],
					'total_floors' => (int)$row[12]
				];

				$sql = "INSERT INTO market_data 
	            ( 
	                        id, 
	                        address, 
	                        objecttype, 
	                        price, 
	                        square_meter_price, 
	                        house, 
	                        street,
	                        current_floor,
	                        total_floors
	            ) 
	            VALUES 
	            ( 
	                        NULL, 
	                        '{$data['address']}', 
	                        '{$data['objecttype']}', 
	                        {$data['price']}, 
	                        {$data['square_meter_price']}, 
	                        '{$data['house']}', 
	                        '{$data['street']}',
	                        {$data['current_floor']},
	                        {$data['total_floors']}
	            ) 
	            ";
				$status = $this->request($sql); 

				if (!$status) {
					$this->logs->addMessage("error insert row in market_data from google sheet values. row:" . json_encode($row));
					$this->request("ROLLBACK");
					return false;
				}


			}
		}

		$this->request("COMMIT");
		return true;
	}

	/**
	 * Подготавливает данные для загрузки в гугл таблицу
	 * @param Array $sheetValues (выборка из geValues)
	 * @param Array $ranges (можно взять из настроек $settings->sheet_ranges)
	*/
	function prepareForSheet($sheetValues, $ranges) {

		$fixed_fields = 1; //Сколько закрепленных строк. 
		//Объявление тут а не в цикле, что бы сориентироваться на какой мы строке после цикла.
		$range_limit = $ranges['general_update_template']; //поля которые необходимо будет обновить.
		$range_all = $ranges['general_all_template'];  // поля которые добавляются в конец таблиц
		$row_index = 0; //Какая строка сейчас

		$data_out = []; //массив который будет возвращен и передан в updateValues для обновления данных

		//Создаем тестовую таблицу для контроля над тем, какие id-шники есть в нашей гугл таблице
		$status = $this->request("CREATE TEMPORARY TABLE id_sheet(
			id int,
			id_source int,
			row_index int,
			primary key (id, id_source)

		)");

		if (!$status) {
			$this->logs->addMessage("error create temp table id_sheet. reason: " . mysqli_error($this->connect));
			return false;
		}
				

		//Пройдемся по данным в таблицы и отберем записи которые необходимо обновить
		foreach ($sheetValues as $index => $row) {


			//Получаем имя источника по хинту
			$source = $this->getFirst("select id, name from sources where hint='{$row[1]}'");

			//Обновим данные в таблице перед этим пропустим все через clearString

			/* Обновление данных в таблице */
			//Может в констркторе
			$fields_update = [
				"status" => $this->data->clearString($row[2]),
				"comments" => $this->data->clearString($row[3]),
				"auction" => $this->data->clearString($row[4]),
				"bonus" => $this->data->clearString($row[5]),
				"owners" => $this->data->clearString($row[6]),
				"registered" => $this->data->clearString($row[7]),
				"check_out" => $this->data->clearString($row[8]),
				"constitutive" => $this->data->clearString($row[9]),
				"price_sale" => $this->data->clearString($row[10]),
				"target_profit" => $this->data->clearString($row[11])
			];

			$string_update = ''; //Для формирования строки запроса
			foreach ($fields_update as $column => $value) {
				$string_update .= "{$column}='{$value}'";

				//Добавляем запятую на каждый элемент кроме последнего
				if (next($fields_update) !== false) $string_update .= ",";
			}

			$status = $this->request("update general_data gd set ". $string_update . " where (id,id_source)=({$row[0]},{$source['id']})");
			if (!$status)
				$this->logs->addMessage("error update fields with google sheet. input_data: " . json_encode($fields_update) . ".reason: " . mysqli_error($this->connect));
			
			/* Обновление данных в таблице */

			//Добавляем айдишку в временную таблицу, что бы понимать какие данные новые, а какие обновляем
			$row_index = $index+$fixed_fields+1;  //+1 так как отсчет с 0;
			$status = $this->request("insert into id_sheet(id, id_source, row_index) values ({$row[0]},{$source['id']}, {$row_index})");

			if (!$status)
				$this->logs->addMessage("error insert index ({$row[0]},{$source['id']}) in id_sheet. reason: " . mysqli_error($this->connect));
		}

		//Выборка всех наших данных (в т ч. и тех которые в гугл таблице)
		$data_update = $this->getAds(
			[
				"fields" => "gd.*, id_s.id as sheet_id, so.hint, so.link_prefix, so.name source_name", 
				"custom_joins" => "inner join id_sheet id_s on (id_s.id,id_s.id_source)=(gd.id,gd.id_source) order by id_s.row_index" //Обязательно сортируй по row_index
			]); //что нужно обновить

		$data_insert = $this->getAds(
			[
				"fields" => "gd.*, id_s.id as sheet_id, so.hint, so.link_prefix, so.name source_name", 
				"custom_joins" => "left join id_sheet id_s on (id_s.id,id_s.id_source)=(gd.id,gd.id_source) where id_s.id is null and id_s.id_source is null"
			]); //что нужно добавить в конец
		$data = array_merge($data_update, $data_insert); //Объеденим выборки

		// foreach ($data as $index => $ad) {
		// 	echo "\n------------------------\n";
		// 	$ad['row_index'] = $index+$fixed_fields+1;  //+1 так как отсчет с 0
		// 	echo "row_index: {$ad['row_index']} \n";
		// 	echo "id: {$ad['id']} \n";
		// 	$dd = isset($ad['sheet_id']) ? "ДА" : "НЕТ";
		// 	echo "Обновляем: " .  $dd . "\n";
		// }
		// return;

		

		foreach ($data as $index => $ad) {

			//-----Формируем данные для таблицы
			$ad['row_index'] = $index+$fixed_fields+1;  //+1 так как отсчет с 0

			//рассчет рыночной цены
			$market_data = $this->getMarketPrice($ad['id'], $ad['id_source']);
			$server_address = "http://redmit.beget.tech/parser_v2/"; //пока константа потом придумаем что то с настройками

			$ad['description'] = preg_replace('!\s+!', ' ', $ad['description']); //Заменяем все пробелы на один

			//Формируем очередную строку, сразу все что точно пойдет в таблицу
			$row = [
				$ad['price_actual'], //текущий прайс
				"=(N{$ad['row_index']}/P{$ad['row_index']})-1", //Изменение. Формула =(цена тек./цена нач)-1
				$ad['price_start'], //начальный прайс
				$ad['square_meter_price'], //цена квадратного метра
				$market_data['price'] == null ? "Мало данных" : $market_data['price'], //Рыночная цена кв.метра
				"=(Q{$ad['row_index']}/R{$ad['row_index']})-1", //Соотношение. Формула = (цена кв метра / рыночная цена кв) - 1
				$market_data['accuracy'] == 0 ? "Мало данных" :  "{$market_data['accuracy']}%", //Точность (%)
				$ad['views'] == null ? "Мало данных" : $ad['views'], //просмотров вчера
				$ad['views_all'], //просмотров всего
				$ad['ad_remove'] == null ? "Продается" : $ad['ad_remove'], //дата снятие || продается
				$ad['ad_published'], //дата публикации
				$ad['ad_added'], //дата добавления
	        	$ad['address'], //адрес
	        	$ad['phones'] == "" ? "нет данных" : $ad['phones'], //телефоны
	        	$ad['complex_name'] == "" ? "Нет данных" : $ad['complex_name'], //Название комплекса
	        	$ad['repair'] == "" ? "Нет данных" : $ad['repair'], //Ремонт
	        	$ad['balconies'] == "" ? "Нет данных" : $ad['balconies'], //Балкон
	        	$ad['restroom'] == "" ? "Нет данных" : $ad['restroom'], //Туалет
	        	$ad['ceiling_height'] == "" ? "Нет данных" : $ad['ceiling_height'], //Высота потолка
	        	$ad['objecttype'],  //
	        	$ad['building_year'], //
	        	$ad['total_area'], //
	        	$ad['current_floor'] . '/' . $ad['total_floors'], // Этаж
	        	$ad['description'], //
	        	$ad['with_photos'] == 1 ? '=HYPERLINK("' . $server_address . $ad['source_name'] . '/images/' . $ad['id'] . '"; "Перейти")' : 'Фото удалены'
			];

			//Если необходимы все данные в выборке - добавим их
			if ($ad['sheet_id'] == null) {
				//Если ссылки нет - берем префикс. Иначе - ссылку
				if($ad['link'] == '')
					$link = $ad['link_prefix'] . $ad['id'];
				else 
					$link = $ad['link'];

				//Добавляем элементы в начало массива
				array_unshift($row, 

					$ad['id'],
					$ad['hint'],

					/* Ручные поля */
					$ad['status'], //статус
					$ad['comments'], //Комментарии
					$ad['auction'], //Торг
					$ad['bonus'], //Бонус
					$ad['owners'], //Собственники
					$ad['registered'], //Прописанные
					$ad['check_out'], //Выписаться
					$ad['constitutive'], //Правиоустанавливающие
					$ad['price_sale'],
					$ad['target_profit'],
					/* Ручные поля */

					'=HYPERLINK("' . $link . '"; "Перейти")' //Ссылка на объявление
				);
			}

			//-----Формируем данные для таблицы

			//Если данные не новые => установим диапазон с ограничениями - иначе все данные обновляем
			$data_out[] = [
				"range_template" => isset($ad['sheet_id']) ? $range_limit : $range_all,
				"data" => $row,
				"row_index" => $ad['row_index']
				
			];



		}



		//Дропнуть временную таблицу
		$status = $this->request("DROP TEMPORARY TABLE id_sheet");
		if (!$status)
			$this->logs->addMessage("error drop temporary table id_sheet. reason: " . mysqli_error($this->connect));

		//Дата out объеденяем по N строк. И вставляем в rande, нужный индекс.
		//и устаналвиваем уже на этом месте range. arange_template - можно убрать
		// foreach ($data_out as $data_row) {
		// 	echo "\n---------------------------\n";
		// 	echo "template: {$data_row['range_template']} \n"; 
			
		// 	if (count($data_row['data']) > 25)
		// 		echo "id: {$data_row['data'][0]}. source: {$data_row['data'][1]} \n"; 
		// 	else 
		// 		echo "id_source: {$data_row['data'][24]} \n"; 

		// 	echo "row index: {$data_row['row_index']} \n"; 
		// 	echo "\n---------\n";
		// }
		// return;

		$data_out_chunks = []; //Объяеденим данные по N элементов
		$data_out_lenpart = 100; //Сколько будет элементов в одной части
		$chunk = []; //Очередная часть будет хранится тут
		$chunk_data = [
			"start_index" => $data_out[0]['row_index'],
			"range_template" => $data_out[0]['range_template']

		]; //Информация о очередной части (индексы, шаблон, диапазон и прочее тут)


		foreach ($data_out as $i => $part)  {

			$template_diff = ($i == count($data_out)-1 ? true: $part['range_template'] != $data_out[$i+1]['range_template']);
			$is_last = count($chunk) == $data_out_lenpart || $i == count($data_out)-1 || $template_diff;

			$chunk[] = $part['data']; //Добавляем к списку очередной элемент
			
			if ($is_last) {
				//Формируем очередную часть данных
				$chunk_data['end_index'] = $part['row_index'];

				//Определяем данные
				$data_out_chunks[] = [
						"range" => vsprintf($chunk_data['range_template'], [$chunk_data['start_index'], $chunk_data['end_index'] ]),
						"data" => $chunk
				];
				

				$chunk = []; //Возвращаем сhunk в изначальное состояние

				if (isset($data_out[$i+1])) {

					$chunk_data = [
						"start_index" => $data_out[$i+1]['row_index'], //Следующая строка 
						"range_template" => $data_out[$i+1]['range_template'] //
					];

				}
					

			}



		}

		// foreach ($data_out_chunks as $rows) {
		// 	echo "\n---------------------------\n";
		// 	echo "row range: {$rows['range']} \n";

		// 	echo "\nData:\n";
		// 	foreach ($rows['data'] as $row) {
				
		// 		if (count($row) > 25) 
		// 			echo "id: {$row[0]}. source: {$row[1]}. formula: {$row['14']} \n"; 
		// 		else 
		// 	 		echo "id_source: {$row[24]}. formula: {$row['1']} \n"; 
		// 	}
		// }
		// return;
		

		return $data_out_chunks;

	}



	/*
	  *sql_data = [values => [key => value], template => '..qdwqd %s'
	  *В sql_data содержится информация о sql запросе
	  * - values 
	  	   - ключ - это столбик таблицы в гугле (номер)
	       - значение - название столбика в таблице которую обновляем
	  * - template куда подставятся все эти поля для обновления

	  **И подстраивается под 2 запроса только
	  - INSERT значит используем констркуию (culumn) (values)
	  - UPDATE значит используем конструкцию update .. set a = 'b'
	  ***Если поле строковое - дополнительно обернуть в ковычки(так как могут быть еще и числовые)

	*/ 
	function sync_with_sheet($sql_data, $sheetValues) {

	}

}

?>