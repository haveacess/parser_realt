<?php

/*
	Все что позволяет управлять страницами
	Обращаем внимание - МЕТОДЫ ТОЛЬКО ДЛЯ ЦИАН!
 */

namespace Parser\Cian;

class Pagination
{
	private $data; //Данные с которыми мы работаем

	function __construct($data) {
		$this->data = $data;
	}

	/**
	 * Изменяет страницу в данных на новую
	 * @param int $page
	 * @return bool
	*/
	function changePage($page) {

		$array = json_decode($this->data, true);
		$array['jsonQuery']['page']['value'] = $page;
		$this->data = json_encode($array);

		return true;

	}

	/**
	 * Получает измененные данные
	 * @return string
	*/
	function getData() {
		return $this->data;
	}


}

?>