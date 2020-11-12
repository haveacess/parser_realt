<?php

/*
	Работа с изображениями.
 */
namespace Parser;


class Images
{

	private $path; //Куда сохраняем / где храним изображения

	function __construct($path) {

		$this->path = $path;
	}

	/**
	 * Загружает картинки объявлений
	 * @param array $ads_data
	*/
	function download($ads_data) {

		foreach ($ads_data as $ad) {

			//Создаем папку для этого изображеия (Учитываем айди и источник)
			$folder = $this->path . "{$ad['source']}/images/{$ad['id']}/";
			mkdir($folder);

			//Грузим наши изображения в папку
			foreach ($ad['photos'] as $index => $photo_link) {
				$photo = file_get_contents($photo_link);
				file_put_contents($folder . "/{$index}.jpg", $photo);
			}

		}	
	}

	/**
	 * Удаляет картинки вместе с папкой
	 * @param array $ads_data
	*/
	function remove($ads_data) {

		foreach ($ads_data as $ad) {
			$folder = $this->path . "{$ad['source']}/images/{$ad['id']}/";
			//Удаляем все файлы в папке
			array_map('unlink', glob("$folder/*.*"));
			rmdir($folder); //Удаляем саму папку
		}	
		
	}


}

?>