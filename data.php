<?php

/*
	Все благодаря чему ты взаимодействуешь с данными  
 */

namespace Parser;

class Data
{
	
	/**
	 * Преобразует слово сегодня или вчера в текущую дату.
	   Иначе, если строка к условию выше не относится - возвращает ее.
	 * @param string $string_date
	 * @return string
	*/
	function getDate($string_date) {
	
		$day = explode(' ', $string_date)[0];
		$date = null; //formated date

		switch (mb_strtolower($day)) {
			case 'сегодня':
				$date = date("d F");
				break;
			case 'вчера':
				$date = date("d F",strtotime("-1 day"));
				break;
			default:
				$date = $string_date;
				break;
		}

		return $date;
	}


	/**
	 * Разбирает строку с адресом и извлекает оттуда 
	   улицу и дом
	 * @param string $address
	 * @return array
	*/
	function decomposeAddress($address) {

		$data = explode(',', $address);


		$house = trim($data[count($data)-1]); //last
		preg_match_all('/[\d]+/m', $house, $matches, PREG_SET_ORDER, 0);
		$house = $matches[0][0];

		if ($house == null) {
			$house = 0;
			$street = trim($data[count($data)-1]);
		}
		else {
			$street = trim($data[count($data)-2]);
		}

		

		$street = str_replace('ул.', '', $street);
		$street = str_replace('улица', '', $street);
		$street = trim($street);
		

		return [
			'street' => $street,
			'house' => $house
		];
	}

	/**
	 * Обрезаем +7 в номере телефона
	 * @param string $phone
	 * @return string
	*/
	function trimPhone($phone) {

		return preg_replace('/[+][7]/m', '', $phone);

	}


	/**
	 * Очищаем строку от посторонних символов. 
	 * На выходе оставим только буквы и знаки препинания, двоеточие и %
	 * @param string $string
	 * @return string
	*/
	function clearString($string) {

		$string = preg_replace('/[^A-Za-zА-Яа-яёЁ0-9.,?!()%\-:\s]/um', '', $string);
		$string = preg_replace('/\s{2,}/um', ' ', $string);

		return $string;

	}

	/**
	 * Возвращает только цифры из строки
	 * @param string $string
	 * @return int
	*/
	function getDigit($string) {

		return preg_replace('/\D+/m', '', $string);

	}




	
}

?>