<?php

namespace Parser;

class Logs 
{
	
	private $path_folder; //Путь к папке с логами
	private $module_name; //Модуль (опц.) например для обозначения откуда именно ошибка


	function __construct($path_folder, $module_name='') {

		$this->path_folder = $path_folder;
		$this->module_name = $module_name;

		//create directory if need
		if (!file_exists($this->path_folder) ) {
			echo "dir for logs not found.. created \n";
		    mkdir($path_folder, 0777);
		}

	}

	/**
	 * Пишет в файл очередное сообщение
	 * Где название файла - текущая дата
	 * А очередная строка начинается с текущее дата-время
	 * @param string $text
	*/
	function addMessage($text) {

		$date = date("Y-m-d");
		$time = date("H:i:s");
		$path_file = "{$this->path_folder}/{$date}.txt";

		$fp = fopen($path_file, 'a'); //a = append mode
		fwrite($fp, "[{$date} {$time}]. {$this->module_name}. {$text} \n");
		fclose($fp);
	}
}

?>