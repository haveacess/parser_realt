<?php

class ProxyMarket 
{
	
	private $apikey; //Наш апи ключ для работы


	function __construct($apikey) {

		$this->apikey = $apikey;

	}

	/**
	 * Получаем список прокси, где $fields - только поля которые нам необходимы
	 * $feilds - параметр опционален
	 * @param array $fields
	 * @return array $proxy
	*/
	function getProxy($fields=[]) {

		$response = json_decode(file_get_contents("https://proxy.market/dev-api/list/{$this->apikey}"), true);

		//UL!!ГДЕТ ОУСЛОВИЕ ПО FIELDS

		if ($response['success']) {

			$list = []; //Список наших проксей

			foreach ($response['list']['data'] as $proxy) {
				if ($proxy['active']) //Отбираем только активные прокси
					$list[] = $proxy;
			}

			if (count($fields) > 0) {
				
				//Ключ - field (который нам нужен), 
				//значение как он обозначен в маркете
				$fields_key = [
					"ip" => "ip",
					"port" => "http_port",
					"user" => "login",
					"pass" => "password"
				];

				$new_list = []; //То что вернем

				foreach ($list as $proxy) {
					$item = []; //промежуточный массив

					foreach ($fields_key as $new_field => $field) {
						$item[$new_field] = $proxy[$field];
					}

					$new_list[] = $item;
				}
					
				

				return $new_list;
			}

			return $list;
		}
		else {
			echo "ProxyMarket return bad response: " . json_encode($response);
			return false;
		}

	}
}

?>