<?php

/*
	Все благодаря чему ты взаимодействуешь с данными  
	Обращаем внимание - МЕТОДЫ ТОЛЬКО ДЛЯ АВИТО!
 */

namespace Parser\Avito;

class Data
{
	
	/**
	 * Получает тип объекта исходя из кол-ва комнат
	 * @param string $count_rooms
	 * @return string
	*/
	function getObjectType($count_rooms) {

		switch ($count_rooms) {
			case 1:
				return '1-комн. квартира';
				break;
			case 2:
				return '2-комн. квартира';
				break;
			case 3:
				return '3-комн. квартира';
				break;
			case 4:
				return '4-комн. квартира';
				break;
			case 5:
				return '5-комн. квартира';
				break;
		}
	}

	/**
	 * парсит url и достает оттуда параметр с именем $param_name
	 * @param string $uri
	 * @param string $param_name
	 * @return string
	*/
	function parseUri($uri, $param_name) {
		$uri = urldecode($uri);
		$parametrs_string  = parse_url($uri, PHP_URL_QUERY);

		preg_match_all("/{$param_name}=.*?(?=&|$)/m", $parametrs_string, $matches, PREG_SET_ORDER, 0);

		$param_value = str_replace($param_name . '=', '', $matches[0][0]);
		return $param_value;
	}

	/**
	 * Возвращает этаж и этажность дома используя поле floorInfo
	 * @param string $floorInfo
	 * @return array
	*/
	function getFloor($floorInfo) {

		$floors = explode('из', $floorInfo);

		return [
			"current_floor" => trim($floors[0]),
			"total_floors" => trim($floors[1])
		];

	}

	/**
	 * Возвращает массив ссылок на изображения,
	 * отбирая только те, которые нам необходимы
	 * @param array $media
	 * @return array
	*/
	function getImagesLinks($media) {

		$links = []; //Наши ссылки
		$allowed_types = ['640x480'];

		foreach ($media as $object) {
			
			foreach ($object as $type => $link) {
				if (in_array($type, $allowed_types))
				$links[] = $link;
			}
			
		}

		return $links;
	}

	/**
	 * Возвращает если телефона из аттрибута contacts.list
	 * @param array $list
	 * @return bool
	*/
	function withPhone($list) {

		$search_type = "phone"; //Какой тип ищем

		foreach ($list as $contact) {
			if ($contact['type'] == $search_type)
				return true;
		}

		return false;

	}

}

?>