<?php

/* Небольшой модуль для кэширования объявлений полученных в текущем сенсе. */
namespace Parser;

class Cache 
{
	
	private $data; //Собственно все храним тут

	/**
	 * Передаем источники и формируем из них массивы.
	 * @param array $sources
	*/
	function __construct($sources) {

		foreach ($sources as $name) {
			$this->data[] = [$name => []];			
		}
	}

	/**
	 * Добавляем аттрибуты в кэш
	 * @param array $ad
	 * @param string $source
	*/
	function addData($ad, $source) {
		$this->data[$source][] = $ad;
	}

	/**
	 * Получаем данные из кэша по идентификатору
	 * @param int $id
	 * @param string $source
	*/
	function getData($id, $source) {
		
		foreach ($this->data[$source] as $ad) {
			if ($ad['attributes']['id'] == $id)
				return $ad;
		}

		return false;
	}

	/**
	 * Возвращает все объявления из кэша
	 * @return array
	*/
	function getAll() {
		return $this->data;
	}
}

?>