<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once  __DIR__ . '/curl.php';

include_once   __DIR__ . '/config/settings.php';
include_once __DIR__ . '/database.php';
include_once __DIR__ . '/logs.php';
include_once __DIR__ . '/cache.php';

include_once   __DIR__ . '/cian/ad.php';
include_once   __DIR__ . '/avito/ad.php';
include_once   __DIR__ . '/images.php';
include_once   __DIR__ . '/sheet.php';

include_once __DIR__  . '/captcha/anticaptcha.php';
include_once __DIR__  . '/captcha/nocaptchaproxyless.php';
include_once __DIR__  . '/proxy6.php';
include_once __DIR__  . '/proxyMarket.php';
include_once __DIR__  . '/request.php';

//Подключаем зависимости композера
require __DIR__ . '/vendor/autoload.php';

//Инициируем создать сервиса для управления гугл таблицами
$client = new \Google_Client();
$client->setApplicationName('Cian parser');
$client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$client->setAuthConfig(__DIR__ . '/config/credentials.json');
$service = new Google_Service_Sheets($client);



$mode = "production"; //Режим работы test || production

//Инциализируем экземпляры которые понадобятся


$settings = new Parser\Settings($mode);
$db = new Parser\Database($settings->db_config, $settings->path['logs']);
$sheet = new Parser\Sheet($service, $settings->sheet_list['realt_data'], $settings->path['logs']);
$logs = new Parser\Logs($settings->path['logs'], 'Main');
$images = new Parser\Images($settings->path['images']);



$sources = ["cian", "avito"]; //можно и просто getAll(select name from sources)
$cache = new Parser\Cache($sources);


//$proxy = new Proxy6($settings->proxy6_apikey); //Прокси Proxy6
$proxy = new ProxyMarket($settings->proxymarket_apikey); //Прокси ProxyMarket
$proxy_list = $proxy->getProxy(["ip", "port", "user", "pass"]); //Получаем все активные прокси

// $captcha = new NoCaptchaProxyless();
// $captcha->setVerboseMode(true);
// $captcha->setKey($settings->antigate_apikey);

$ad_avito = new Parser\Avito\Ad($settings->path['logs'], $proxy_list);
$ad_cian = new Parser\Cian\Ad($settings->path['logs'], $proxy_list);


/* Проверка работы сайтов */
// for ($i=0; $i < count($proxy_list); $i++) { 
// 	$tt = $ad_avito->getAttributes("2033733670");
// 	var_dump($tt);
// 	$byby = $ad_cian->getAttributes("242730143");
// 	var_dump($byby);
// }
/* Проверка работы сайтов */

$logs->addMessage("Parsing is started");


/* Prepare for start */
for ($i=7; $i>0; $i--) { 
	echo "Prepare for start. Left {$i} seconds \n";
	sleep(1);
}
/* Prepare for start */


//Данные только не забудь вернуть и режим поменять

echo "getting old photos \n";
//Удалить все старые фото и пометить что без фото
$old_photos = $db->getOldPhotos(); //Получим старые фото

if ($old_photos === false) {
	echo "oops. fail delete old photos. \n";
	$logs->addMessage("error getting old photo in db");
}
else {
	echo "old photos received. now deleting old photos \n";
	//Все в порядке. Старые фото получить удалось
	$images->remove($old_photos); //Удаляем папки
	$db->withoutPhotos($old_photos); //Помечаем фото как удаленные
}


//получения значений из маркета и синхронизация их с бд
$market_data = $sheet->getValues($settings->sheet_ranges['market_data_all']);
$market_sync_status = $db->sync_market_data($market_data);
if (!$market_sync_status) {
	echo "oops. fail sync market data. \n";
	$logs->addMessage('error sync market data');
}
else {
	echo "market data successfully synchronized \n";
}



//полученеи объявлений всех, (и активных и нет).
//И если статус == false && reason ad_removed = значит установить как неактивное(логика сейчас)
//Иначе если статус == true ==> установим как активное

//Проверка статуса объявлений. Получаем все объявления в базе.
//Хм. Только если будем парралелить ограничить по источнику $name
$ad_list = $db->getAds([
	"fields" => "gd.id, so.name as source"
	//"is_active" => true
]);


$ad_unactive = []; //список объявлений, в которых нужно поменять статусы
foreach ($ad_list as $ad) {

	echo "check ad ({$ad['source']}, {$ad['id']}) status \n";

	//В зависимости от источника используем экземлпяр для получения аттрибутов
	switch ($ad['source']) {
		case 'cian':
			$attr = $ad_cian->getAttributes($ad['id']);
			break;
		case 'avito':
			$attr = $ad_avito->getAttributes($ad['id']);
			break;
		
		default:
			$logs->addMessage("source {$ad['source']} is not supported. We can`t verify this ad: {$ad['id']}");
			$attr = null;
			break;
	}

	//Проверяем если только валидный источник
	if ($attr != null) {
		if ($attr['status'] == false && $attr['reason'] == "ad_removed") {
			$logs->addMessage("ad ({$ad['source']}, {$ad['id']})  will marked as inactive");
			echo "detected inactive ad ({$ad['source']}, {$ad['id']}) \n";
			$ad['is_active'] = false; //Помечаем стаус как неактивный
			$ad_unactive[] = $ad; //Пушим объявление в список неактивных
		}
		else if ($attr['status'] == true) {
			//Тут еще кэш влепить для attr

			$cache->addData($attr, $ad['source']);

			$logs->addMessage("ad ({$ad['source']}, {$ad['id']})  will marked as active");
			echo "detected active ad ({$ad['source']}, {$ad['id']}) \n";

			$ad['is_active'] = true; //Помечаем стаус как неактивный
			$ad_unactive[] = $ad; //Пушим объявление в список неактивных
		}
	}
}

//Изменяет статус у объявлений
$db->changeStatus($ad_unactive);


//Собираем свежие объявления.
//sources - ищи в начале, где объявляются все экземпляры
$ads_new = []; //Все новые объявления
foreach ($sources as $name) {
	
	switch ($name) {
		case 'cian':
			$ids = $ad_cian->getIds($settings->search_conditions[$name]);
			$attr = []; //Аттрибуты наших объявлений


			if ($ids == null) {
				$ids = [];
				continue; //captcha found
			}

			//Собираем аттрибуты
			foreach ($ids as $id) {

				$ad_info_cache = $cache->getData($id, $name);

				if ($ad_info_cache == false) {
					$ad_info = $ad_cian->getAttributes($id);
				}
				else {
					$logs->addMessage("ad attr ({$id}, {$name}) received from cache");
					echo "ad attr ({$id}, {$name}) received from cache \n";
					$ad_info = $ad_info_cache;
				}

				if ($ad_info['status'])
					$attr[] = $ad_info;
			}

			//Отправляем объвление на обновление.
			//А все вновь доавленные объявления пушим в массив ads_new
			//ads_new позволит загрузить картинки для объявлений в дальнейшем
			$ad_added = $db->update_ads($attr,$name); //Пушим объявления в базу
			if (!$ad_added)
				$logs->addMessage("source: {$name}. fail update ads. ids: " . json_encode($ids));
			else 
				$ads_new = array_merge($ads_new, $ad_added); //Добавляем к списку для загрузки картинок

			break;

		case 'avito':
			$ids = $ad_avito->getIds($settings->search_conditions[$name]);

			if ($ids == null) {
				$ids = [];
				continue; //captcha found
			}
			else {
				//Id на страницах могут повторятся - отберем только уникальные
				$ids = array_unique($ids);
				$ids = array_values($ids); //переиндексировать
			}

			$logs->addMessage("We received ids from avito: ". json_encode($ids));

			$attr = []; //Аттрибуты наших объявлений

			//Собираем аттрибуты
			foreach ($ids as $id) {
				$ad_info_cache = $cache->getData($id, $name);

				if ($ad_info_cache == false) {
					$ad_info = $ad_avito->getAttributes($id);
				}
				else {
					$logs->addMessage("ad attr ({$id}, {$name}) received from cache");
					echo "ad attr ({$id}, {$name}) received from cache \n";
					$ad_info = $ad_info_cache;
				}

				if ($ad_info['status'])
					$attr[] = $ad_info;
			}

			//Отправляем объвление на обновление.
			//А все вновь доавленные объявления пушим в массив ads_new
			//ads_new позволит загрузить картинки для объявлений в дальнейшем
			$ad_added = $db->update_ads($attr,$name); //Пушим объявления в базу
			if (!$ad_added)
				$logs->addMessage("source: {$name}. fail update ads. ids: " . json_encode($ids));
			else 
				$ads_new = array_merge($ads_new, $ad_added); //Добавляем к списку для загрузки картинок
				

			break;
		
		default:
			$logs->addMessage("source {$name} is not supported. We can`t getting ads for this source");
			$ids = [];
			break;

	}

	//Проверяем источник на валидность
	if (count($ids) == 0) {

		echo "something wrong. source {$name} not have ids or this source not supported \n";
		$logs->addMessage("something wrong. source {$name} not have ids or this source not supported");
	}
}

if ($mode == "production") {

	//Грузим фотографии для новых объявлений
	if (count($ads_new) > 0) {
		echo "Good. Will be received how minimum one new ad \n";
		echo "Load photos.. \n";
		$images->download($ads_new);
	}
	else {
		echo "something wrong. Not have new ads \n";
		$logs->addMessage("something wrong. Not have new ads");
	}

}

//-----------Выгрузка данных в таблицу
//подготовка данных для выгрузки в таблицу
$values_general_all = $sheet->getValues($settings->sheet_ranges['general_all']);
$prepared_data_sheet = $db->prepareForSheet($values_general_all, $settings->sheet_ranges);
//И собственно отправить объявления в таблицу

//Выгрузка в таблицу
$sheet->changeTitle('Циан парсер. Идет обновление данных');

$sheet->updateValues($prepared_data_sheet); //Обновляем значения

$last_update = $db->getFirst("select IFNULL(DATE_FORMAT(max(ad_added), '%b %e, %W'), 'Никогда') as last_update from general_data")['last_update'];
$sheet->changeTitle('Циан парсер. Последнее обновление: ' . $last_update);

$logs->addMessage("Parsing is finished");

?>