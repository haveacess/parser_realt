<?php

/*
	Все благодаря чему ты взаимодействуешь с данными  
	Обращаем внимание - МЕТОДЫ ТОЛЬКО ДЛЯ ЦИАН!
 */

namespace Parser\Cian;

class Data
{
	
	/**
	 * Преобразует массив состояший из массивов key:value в строку
	 * Возвращает так же только улицу, дом и полный адрес
	 * @param array $array
	 * @return array
	*/
	function getFullAddress($array) {


		$full_adress = $array[0]['value'];
		$street = null;
		$house = null;


		for ($i=1; $i < count($array); $i++) { 

			$key = $array[$i]['key'];
			$value =  $array[$i]['value'];
			
			switch ($key) {
				case 'location':
					$full_adress .= ', ' . $value;
					break;
				case 'street':
					$full_adress .= ', ' . $value;

					$street = $value;
					$street = str_replace('ул.', '', $street);
					$street = str_replace('улица', '', $street);
					$street = trim($street);
					break;
				case 'house':
					$full_adress .= ', д. '. $value;
					$house  = $value;
					preg_match_all('/[\d]+/m', $house, $matches, PREG_SET_ORDER, 0);
					$house = $matches[0][0];
					break;			
				default:
					$full_adress .= ', ' . $value;
					break;
			}
		}

		return [
			"full_adress" => $full_adress,
			"street" => $street,
			"house" => $house
		];
	}


	/**
	 * Возвращает этаж и этажность дома используя поле floorInfo
	 * @param string $floorInfo
	 * @return array
	*/
	function getFloor($floorInfo) {

		$floors = explode('/', explode(' ', trim($floorInfo))[0]);

		return [
			"current_floor" => $floors[0],
			"total_floors" => $floors[1]
		];

	}

	/**
	 * Возвращает информацию по полной площади используя поле totalARea
	 * @param string $floorInfo
	 * @return array
	*/
	function getTotalArea($string) {

		//$string = explode(' ', $string)[0];
		$string = preg_replace('/[^.,\d]+/m', '', $string); //replace m2
		//$string = trim($string);

		return $string;

	}

	/**
	 * Возвращает массив ссылок на изображения,
	 * отбирая только те, которые нам необходимы
	 * @param array $media
	 * @return array
	*/
	function getImagesLinks($media) {

		$links = []; //Наши ссылки
		$allowed_types = ['photo', 'layout'];

		foreach ($media as $object) {
			
			if (in_array($object['type'], $allowed_types))
				$links[] = $object['previewUrl'];
		}

		return $links;
	}


}

?>