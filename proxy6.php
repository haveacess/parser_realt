<?php

class Proxy6 
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

		$response = json_decode(file_get_contents("https://proxy6.net/api/{$this->apikey}/getproxy?state=active&nokey=true"), true);

		if ($response['status'] == "yes") {

			$list = $response['list'];
			$list_out = [];

			if (count($fields) > 0) {

				foreach ($list as $proxy) {
					
					$new_proxy = [];
					foreach ($proxy as $field => $value) {
						if (in_array($field, $fields))
							$new_proxy[$field] = $value;
					}
					$list_out[] = $new_proxy;
				}

				return $list_out;
			}
			

			return $list;

		}
		else {
			echo "proxy6 return bad response: " . json_encode($response);
			return false;
		}

	}
}

?>