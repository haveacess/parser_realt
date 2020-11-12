<?php

/*
	Все что позволяет управлять страницами
	Обращаем внимание - МЕТОДЫ ТОЛЬКО ДЛЯ Авито!
 */

namespace Parser\Avito;


class Pagination
{
	private $template; //На основе этого шаблона формируем данные
	private $data; //Данные с которыми мы работаем
	private $logs; //Экземпляр для записи логов
	private $search_condiions; //Условия поиска

	function __construct($template, $search_condiions) {
		$this->template = $template;
		$this->search_condiions = $search_condiions;

		$this->changePage(1, 0, '');
	}

	/**
	 * Изменяет страницу в данных на новую
	 * @param int $page
	 * @param int $lastStamp
	 * @param string $nextPageId
	 * @return bool
	*/
	function changePage($page, $lastStamp, $nextPageId) {

		if ($page == 1)
			$this->data = vsprintf($this->template, [$this->search_condiions, $page, '', '']);
		else
			$this->data = vsprintf($this->template, [$this->search_condiions, $page, '&lastStamp=' . $lastStamp, '&pageId=' . $nextPageId]);

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