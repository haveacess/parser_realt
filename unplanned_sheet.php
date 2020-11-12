
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
$logs = new Parser\Logs($settings->path['logs'], 'Main unplanned sheet');
$sheet = new Parser\Sheet($service, $settings->sheet_list['realt_data'], $settings->path['logs']);
//-----------Выгрузка данных в таблицу

//подготовка данных для выгрузки в таблицу
$values_general_all = $sheet->getValues($settings->sheet_ranges['general_all']);


$prepared_data_sheet = $db->prepareForSheet($values_general_all, $settings->sheet_ranges);
//И собственно отправить объявления в таблицу

//exit();

// //Выгрузка в таблицу
if ($mode == "production")
	$sheet->changeTitle('Циан парсер. Идет обновление данных');

$sheet->updateValues($prepared_data_sheet); //Обновляем значения

if ($mode == "production") {
	$last_update = $db->getFirst("select IFNULL(DATE_FORMAT(max(ad_added), '%b %e, %W'), 'Никогда') as last_update from general_data")['last_update'];
	$sheet->changeTitle('Циан парсер. Последнее обновление: ' . $last_update);
}

$logs->addMessage("upload is finished");



?>