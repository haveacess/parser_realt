<?php

/*
	Все для работы с объявлениями
	Обращаем внимание - МЕТОДЫ ТОЛЬКО ДЛЯ ЦИАН!
*/

namespace Parser\Cian;

include_once  __DIR__ . "/.." . '/logs.php';
include_once  __DIR__ . "/.." . '/data.php';
include_once  __DIR__ . "/.." . '/cian/data.php';
include_once  __DIR__ . "/.." . '/cian/pagination.php';
include_once __DIR__  . "/.." . '/request.php';


class Ad
{
	//Эндпоинт со списком объявлений
	private $endpoint_list = "https://api.cian.ru/search-engine/v1/search-offers-mobile-site/";
	
	//Эндпоинт страницы. 
	//Первый параметр -> id объявления для получения инфы
	private $endpoint_page = "https://api.cian.ru/offer-card/v1/get-offer-mobile-site/?dealType=sale&offerType=flat&cianId=%s&noRedirect=true&subdomain=www";

	private $request; //Экземпляр для запросов
	private $timeout; //Время в секундах между запросами
	private $logs; //Экземпляр класса Logs, куда будет все писаться
	private $data; //Манипулятор с данными
	private $data_cian; //Манипулятор с данными циана

	function __construct($path_logs, $proxy_list, $timeout=1) {
		$this->timeout = $timeout;

		$this->request = new \Request($proxy_list);		
		
		//Инциализация экзепляра для логов
		$this->logs = new \Parser\Logs($path_logs, "CIAN Ad");

		$this->data = new \Parser\Data;
		$this->data_cian = new \Parser\Cian\Data;
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

		//Используем объект для поиска
		$search = new \Parser\Cian\Pagination($search_conditions);

		while ($page <= $ads_pages_cnt) {
			sleep($timeout);

			try {

				$page++;
				$search->changePage($page);

				$offers = $this->request->post($this->endpoint_list, $search->getData(), ["Content-Type: application/json"]);
				$pages = json_decode($offers, true);

				//Парсинг не удался, вероятнее капча
				if ($pages == null) {
					$this->logs->addMessage('offers is null. probably captcha found. offers body: ');
					$this->logs->addMessage($offers);

					return null;
				}

				/* calculate only first time */
				if ($page == 1) {				
					$ads_cnt = $pages['aggregatedOffersCount']; //get count all ads
					$ads_pages_cnt = $ads_cnt / count($pages['offers']); //get count pages
					$ads_pages_cnt = (int)$ads_pages_cnt;

					if ( ($ads_cnt%$ads_pages_cnt) > 0)
						$ads_pages_cnt++; //if how minimum one element left, exist also one page
				}

				echo "all offers found: " . $ads_cnt . "\n";
				echo "count pages: " . $ads_pages_cnt . "\n";
				echo "current page: " . $page . "\n";

				echo "\nOffers:\n";

				for ($i=0; $i < count($pages['offers']); $i++) { 

					echo $pages['offers'][$i]['cianId'] . "\r\n";
					$ads_ids[] = $pages['offers'][$i]['cianId'];
				}

			}
			catch (Exception $e) {
				$this->logs->addMessage("Error parsing id list: " . $e->getMessage());
				$this->logs->addMessage("body json (offers): " . $offers);
				$this->logs->addMessage("page: " . $page . ". pages count: ". $ads_pages_cnt . ". ads count: " . $ads_cnt);
			}
			

		}

		echo "ads count: " . $ads_cnt . " cian id cnt: " . count($ads_ids) . "\n";

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
			//echo $this->request->get(sprintf($this->endpoint_page, $id));

			if ($page == null) {
				$this->logs->addMessage('captcha found. id ad: ' . $id);
				return [ "status" => false,"reason" => "captcha_found", "attributes" => ["id" => $id] ];
			}

			if (isset($page['errors'])) {
				$this->logs->addMessage("Fail parsing id: {$id}. body: " . json_encode($page));
				return [ "status" => false,"reason" => "error_found", "attributes" => ["id" => $id] ];
			}
			else {
				//Можно парсить аттрибуты
				if ($page['offer']['status'] != "published") {
					//Снято с публикации
					$this->logs->addMessage("ad {$id} has been changed status. New status: {$page['offer']['status']}");
					return [ "status" => false,"reason" => "ad_removed", "attributes" => ["id" => $id] ];
				}

			}

			//Достаем инфу по адресу
			$address_data = $this->data_cian->getFullAddress($page['offer']['geo']['address']);
			//Достаем инфу по этажности
			$floor_data = $this->data_cian->getFloor($page['offer']['features']['floorInfo']);


			$attr = [
				/* parsing only first time */
				'id' => $id,    //Описание так же очищаем от всего не нужного
				'description' => $this->data->clearString(implode(' ', $page['offer']['description'])), //строка
				'link' => "", //Используется префикс.
				'address' => $address_data['full_adress'], //строка
				

				/* This attributes list can be not exist */
				'complex_name' => isset($page['offer']['building']['features']['name']) ? $page['offer']['building']['features']['name'] : null, //название ЖК строка
				'repair' => isset($page['offer']['general']['repair']) ? $page['offer']['general']['repair'] : null, //ремонт строка
				'balconies' => isset($page['offer']['general']['balconies']) ? $page['offer']['general']['balconies'] : null, //балкон строка
				'restroom' => isset($page['offer']['general']['restroom']) ? $page['offer']['general']['restroom'] : null, //Санузел строка
				'ceiling_height' => isset($page['offer']['general']['ceilingHeight']) ? $page['offer']['general']['ceilingHeight'] : null, //число дробное
				/* This attributes list can be not exist */	

				'objectType' => $page['offer']['features']['objectType'], //Однокмнатная двукомнатная и тд
				
				'ad_published' => $this->data->getDate($page['offer']['meta']['addedAt']), //Храним как строку
				'total_area' => $this->data_cian->getTotalArea($page['offer']['features']['totalArea']), //число
				'photos' => $this->data_cian->getImagesLinks($page['offer']['media']),
				
				/* parsing always */
				'price_actual' => $page['offer']['price']['value'], //число
				'price_start' => $page['offer']['price']['value'], //число
				'square_meter_price' => $this->data->getDigit($page['offer']['additionalPrice']), //строка.
				'phones' => $this->data->trimPhone(implode(', ', $page['offer']['phones'])), //строка
				'views_all' => $page['offer']['meta']['views'], //число
				'ad_remove' => "null"
				//'ad_type' => -1, //будет строка платное, премиум, топ	.while not using						
			];


			//Если есть история объявления - возьмем оттуда цену и дату публикации
			if (isset($page['priceChange']))
				if (count($page['priceChange']) > 0) {
					//cnage price and date publicaiton
					$attr['ad_published'] = $page['priceChange'][0]['date'];
					$attr['price_start'] = $page['priceChange'][0]['price'];
				}

			//Если цена не в рос. рублях переведем ее в эту валюту
			if ($page['offer']['price']['currency'] != "rur") {
				//change all prices

				$attr['price_start'] = $this->data->getDigit($page['offer']['rublesPrice']);
				$attr['price_actual'] = $this->data->getDigit($page['offer']['rublesPrice']);

				$attr['square_meter_price'] = intdiv($attr['price_actual'],$attr['total_area']); //recalc square_meter_price

			}

			$attr['street'] =  $address_data['street'];
			$attr['house'] =  $address_data['house'];


			if (isset($page['offer']['houseInfo']['info']['buildYear']))
				$attr['building_year'] = "построен: " . $page['offer']['houseInfo']['info']['buildYear'];
			else if (isset($page['offer']['building']['features']['deadline']))
				$attr['building_year'] = "срок сдачи: " . $page['offer']['building']['features']['deadline'];
			else
				$attr['building_year'] = 'нет данных';

			$attr['current_floor'] = $floor_data['current_floor'];
			$attr['total_floors'] = $floor_data['total_floors'];

			return ["status" => true, "attributes" => $attr];


		}
		catch (Exception $e) {


			return ["status" => false, "reason" => "found_exception"];
			$this->logs->addMessage("error: " . $e->getMessage());
			$this->logs->addMessage("body json (page): " . json_encode($page));
			$this->logs->addMessage("ad id : " . $id);
		}

		
	}


}

?>