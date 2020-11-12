<?php

/*
	Все для работы с объявлениями
	Обращаем внимание - МЕТОДЫ ТОЛЬКО ДЛЯ АВИТО!
*/

namespace Parser\Avito;

include_once   __DIR__ . "/.." . '/logs.php';
include_once   __DIR__ . "/.." . '/data.php';
include_once   __DIR__ . "/.." . '/avito/data.php';
include_once   __DIR__ . "/.." . '/avito/pagination.php';
include_once __DIR__  .  "/.." . '/request.php';

class Ad
{
	//https://m.avito.ru/api/9/items?key=af0deccbgcgidddjgnvljitntccdduijhdinfgjgfjir&params[201]=1059&params[549][]=5697&params[549][]=5696&categoryId=24&locationId=638920&sort=date&page=2&lastStamp=1602973680&display=list&limit=30&pageId=H4sIAAAAAAAAA0u0MrSqLrYyNLRSKskvScyJT8svzUtRss60MjQzN7euBQDvBlOaIAAAAA

	//Первый самый парамтер наш - параметры поиска. берем из настроек
	//&params[201]=1059&params[549][]=5697&params[549][]=5696&categoryId=24&locationId=638920
	
	//page - это номер страницы (начиная с 1)
	//&lastStamp=1602973680 - дальше это. Первый запрос без него, потом подставить значение из ответа
	//после лимита идет pageId токен


	//Эндпоинт со списком объявлений
	private $endpoint_list = "https://m.avito.ru/api/9/items?key=af0deccbgcgidddjgnvljitntccdduijhdinfgjgfjir%s&sort=date&page=%s%s&display=list&limit=30%s";

	//Эндпоинт страницы. 
	//Первый параметр -> id объявления для получения инфы
	private $endpoint_page = "https://m.avito.ru/api/14/items/%s?key=af0deccbgcgidddjgnvljitntccdduijhdinfgjgfjir&action=view";
	private $endpoint_phone = "https://m.avito.ru/api/1/items/%s/phone?key=af0deccbgcgidddjgnvljitntccdduijhdinfgjgfjir";

	private $request; //Экземпляр для запросов
	private $timeout; //Время в секундах между запросами
	private $logs; //Экземпляр класса Logs, куда будет все писаться
	private $data; //Манипулятор с данными
	private $data_avito; //Манипулятор с данными циана

	function __construct($path_logs, $proxy_list, $timeout=2) {
		$this->timeout = $timeout;

		$this->request = new \Request($proxy_list);	
		
		//Инциализация экзепляра для логов
		$this->logs = new \Parser\Logs($path_logs, "Avito Ad");

		$this->data = new \Parser\Data;
		$this->data_avito = new \Parser\Avito\Data;
	}

	/**
	 * Получает все идентификаторы со страниц 
	 * которые соответствуют условиям поиска
	 * @param string $search_conditions
	 * @return array || null
	*/
	function getIds($search_conditions) {

		$page = 0; //Текущая страница
		$ads_pages_cnt = 0; //ВСего страниц
		$ads_ids = []; //Список айдишек
		$ads_cnt = 0; //Сколько объявлений было найдено
		$timeout = $this->timeout; //Время между запросами

		$pagination_info = ['lastStamp' => '', 'nextPageId' => ''];

		//Используем объект для поиска
		$search = new \Parser\Avito\Pagination($this->endpoint_list, $search_conditions);

		while ($page <= $ads_pages_cnt)  {
			sleep($timeout);

			try {

				$page++;
				$search->changePage($page, $pagination_info['lastStamp'], $pagination_info['nextPageId']);
				
				
				//--------пробуем 4 раза распарсить нашу страницу
				$try_parsepage = 4; //Кол-во попыток

				//Должно или истечь кол-во попыток или распарсится значение
				while ($try_parsepage--) {
					
					//тут будем гет слать
					$offers = $this->request->get($search->getData());
					$pages = json_decode($offers, true);

					if ($pages != null)
						break; //выходим до того как попытки истекут
				}
				//--------пробуем 2 раза распарсить нашу страницу
				
				//echo "get: \n\n" . $search->getData() . " \n\n";
				$this->logs->addMessage("getting ids from page: " . $search->getData());
				//$this->logs->addMessage("offers: " . json_encode($offers) );

				//Парсинг не удался, вероятнее капча
				if ($pages == null) {
					$this->logs->addMessage('offers is null. probably captcha found. offers body: ' . json_encode($offers));
					$this->logs->addMessage($offers);
					return null;
				}

				$pagination_info = [
					'lastStamp' => $pages['result']['lastStamp'],
					'nextPageId' => $pages['result']['nextPageId']
				];
				

				//calculate only first time 
				if ($page == 1) {				
					$ads_cnt = $pages['result']['mainCount']; //get count all ads
					$ads_pages_cnt = $ads_cnt / 30; //get count pages, 30 (limit) - ads per page
					$ads_pages_cnt = (int)$ads_pages_cnt;

					if ( ($ads_cnt%$ads_pages_cnt) > 0)
						$ads_pages_cnt++; //if how minimum one element left, exist also one page

					$this->logs->addMessage("detected {$ads_pages_cnt} pages (30 el per page)");
				}

				echo "all offers found: " . $ads_cnt . "\n";
				$this->logs->addMessage("all offers found: {$ads_cnt}");
				echo "count pages: " . $ads_pages_cnt . "\n";
				echo "current page: " . $page . "\n";
				$this->logs->addMessage("current page {$page}");

				echo "\nOffers:\n";

				for ($i=0; $i < count($pages['result']['items']); $i++) { 

					//Отбираем только объявления
					if ($pages['result']['items'][$i]['type'] == "item") {
						echo $pages['result']['items'][$i]['value']['id'] . "\n";
						$ads_ids[] = $pages['result']['items'][$i]['value']['id'];
					}
					
					
				}

			}
			catch (Exception $e) {
				$this->logs->addMessage("Error parsing id list: " . $e->getMessage());
				$this->logs->addMessage("body json (offers): " . $offers);
				$this->logs->addMessage("page: " . $page . ". pages count: ". $ads_pages_cnt . ". ads count: " . $ads_cnt);
			}
			

		}

		echo "ads count: " . $ads_cnt . " avito id cnt: " . count($ads_ids) . "\n";
		$this->logs->addMessage("checksum: ads count: {$ads_cnt}, factical count: " . count($ads_ids));

		if (count($ads_ids) - $ads_cnt >= 0) {
			//all so ok
		}
		else {
			$this->logs->addMessage("didn't match cnt ads (ads_ids: " . count($ads_ids) ." vs. ads_cnt {$ads_cnt}), ads pages count: {$ads_pages_cnt}");
			$this->logs->addMessage("ads_ids list: " . json_encode($ads_ids));
		}

		return $ads_ids;
	}

	/**
	 * Получаем поля объявления используя его id,
	 * Если все объявление на публикации и аттрибуты получены статус  = true, attributes = [],
	 * Если объявление снято с публикации или распарсить не удалось status == false, reason =...
	 * reason = [captcha_found, error_found, ad_removed]
	 * @param int $id
	 * @return array
	*/
	//Если статус == false  - вывести причину.
	//если причина == ad_removed - пометить объявление как удлаеннное
	//Иначе (статус == true = значит вернем аттрибуты и распарсим их)
	function getAttributes($id) {

		sleep($this->timeout);

		//И постоянные/временные аттрибуты думаю взять из запросов

		echo "getting info by ad id: " . $id . "\n";		

		try {

			$page = json_decode($this->request->get(sprintf($this->endpoint_page, $id) ), true);
			//$page = json_decode(file_get_contents(__DIR__ . '/test_json/page.json'), true);
			//echo file_get_contents(sprintf($endpoint, $id));

			if ($page == null) {
				$this->logs->addMessage('captcha found. id ad: ' . $id);
				return [ "status" => false,"reason" => "captcha_found", "attributes" => ["id" => $id] ];
			}

			if (isset($page['error'])) {
				$this->logs->addMessage("Fail parsing id: {$id}. body: " . json_encode($page) );
				return [ "status" => false,"reason" => "error_found", "attributes" => ["id" => $id] ];
			}
			else {
				//Можно парсить аттрибуты
				if (isset($page['status']))
					if ($page['status'] == "closed" || $page['status'] == "expired") {
					//Снято с публикации
					$this->logs->addMessage("ad {$id} has been changed status. New status: {$page['status']}");
					return [ "status" => false,"reason" => "ad_removed", "attributes" => ["id" => $id] ];
				}

			}

			//парсим телефон еcли только телефон есть
			if ($this->data_avito->withPhone($page['contacts']['list']))
				$phones = $this->getPhone($id);
			else $phones = ""; //нет номера, оставляем пустую строку
			

			$address_data = $this->data->decomposeAddress($page['address']);

			$attr = [
				'id' => $id,
				'description' => $this->data->clearString($page['description']), //Описание
				'link' => $page['sharing']['url'], //Ссылка на объявление
				'address' => $page['address'], //Адрес
				'total_area' => 0, //Общая площадь
				'objectType' => '', //Однокмнатная двукомнатная и тд
				'ad_published' => strftime("%d %B %Y", $page['time']), //Храним как строку
				'views_all' => $page['stats']['views']['total'],
				'building_year' => 'Нет данных', //Год постройки

				'phones' => $this->data->trimPhone($phones), //строка

				'street' => $address_data['street'],
				'house' => $address_data['house'],

				'balconies' => null, //Балкон
				'complex_name' => null, //Название комплекса
				'repair' => null, //Ремонт
				'restroom' => null, //Санузел
				'ceiling_height' => null, //Потолок


				'current_floor' => '0', //текущий этаж
				'total_floors' => '0', //всего
				'price_start' => $this->data->getDigit($page['price']['value']),
				'price_actual' => $this->data->getDigit($page['price']['value']),
				'photos' => $this->data_avito->getImagesLinks($page['images']),	
				'ad_remove' => "null" //по умолчанию
				//'ad_type' => -1, //будет строка платное, премиум, топ	.while not using

			];

			$floor_info = ''; //Сюда запишем наш этаж
			//Определяем некоторые параметры
			foreach ($page['parameters']['flat'] as $field) {

				
				switch ($field['title']) {

					case 'Название новостройки':
						$attr['complex_name'] = $field['description'];
						break;
					case 'Балкон или лоджия':
						$attr['balconies'] = $field['description'];
						break;
					case 'Общая площадь':
						$attr['total_area'] = explode(' ', $field['description'])[0]; //Оставим только число
						
						break;
					case 'Количество комнат':
						$attr['objectType'] = $this->data_avito->getObjectType($field['description']);
						break;
					case 'Срок сдачи':
						$attr['building_year'] = "срок сдачи: " . $field['description'];
						break;
					case 'Этаж':
						$floor_info = $field['description'];
						break;


					
					default:
						# code...
						break;
				}
			}

			//Достаем инфу по этажности
			$floor_data = $this->data_avito->getFloor($floor_info);
			$attr['current_floor'] = $floor_data['current_floor'];
			$attr['total_floors'] = $floor_data['total_floors'];

			//Рассчет цены за метр
			$attr['square_meter_price'] = (int)($attr['price_actual']/$attr['total_area']);

			$attr['total_area'] = str_replace('.', ',', $attr['total_area']); //Как разделитель лучше использовать запятую

			//Установим старую цену, если имеется
			if (isset($page['price']['value_old']))
				$attr['price_start'] = $this->data->getDigit($page['price']['value_old']);


			return ["status" => true, "attributes" => $attr];


		}
		catch (Exception $e) {


			return ["status" => false, "reason" => "found_exception"];
			$this->logs->addMessage("error: " . $e->getMessage());
			$this->logs->addMessage("body json (page): " . json_encode($page));
			$this->logs->addMessage("ad id : " . $id);
		}

		
	}

	/**
	 * получаем телефон в объявлении. передаем id объявления
	 * try_num номер попытки
	 * @param int $id
	 * @return array || false
	*/
	function getPhone($id, $try_num=1) {

		$max_try = 10;
		$sleep = 4; //Между попытками какая задержка (вобще хорошо бы привязаться к прокси листу целое значени (15 / кол-во проксей))

		if ($try_num == $max_try) {
			$this->logs->addMessage("phone. all possible attempts were used. id: {$id}");
			return "парсинг не удался";
		}

		sleep($sleep);

		//Получим телефоны
		//переменная $status будет изменена на код ответа
		$status = 0;
		$phones_data = json_decode($this->request->get(sprintf($this->endpoint_phone, $id), $status), true);

		//только в таком случае парсим и делаем что то с номером
		if ($status == 200) {
			$value = $this->data_avito->parseUri($phones_data['result']['action']['uri'], 'number');
			return $value;
		}
		else {
			return $this->getPhone($id, $try_num+1); //получить номер не удалось. пробуем еще раз
		}

	}


}

?>