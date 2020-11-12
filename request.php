<?php

class Request 
{
	
	private $proxy_list; //Понадобится если были установлены прокси
	private $count_request; //считает запросы, может понадобится если будем распределять нагрузку между проксями

	/**
	 * Передаем прокси если есть. proxy_list опционален
	 * @param array $proxy_list. object format: {"ip", "port", "user", "pass"}
	*/
	function __construct($proxy_list=[]) {

		$this->proxy_list = $proxy_list;
		$this->count_request = 0; 

	}

	/**
	 * Возвращает очередной объект прокси листа
	 * @return array $proxy
	*/
	function getNextProxy() {	

		//Рандомизируем задержку от min до макс (сек)
		$sleep_min = 0;
		$sleep_max = 3 * 1000000; 	

		$sleep_micro = random_int($sleep_min, $sleep_max);
		echo "sleep: " . ($sleep_micro / 1000000) . " seconds \n";
		usleep($sleep_micro);

		//Мешаем прокси после каждой итерации
		// if ($this->count_request == count($this->proxy_list)) {
		// 	echo "shuffle proxy list.. \n";
		// 	shuffle($this->proxy_list);
		// }

		//Определяем номер очередной прокси
		$i = $this->count_request%count($this->proxy_list);
		$proxy = $this->proxy_list[$i];

		$this->count_request++;

		return $proxy;
	}

	/**
	 * Отправляет get запрос curl
	 * status_coude(optional) - если указан переменная status_code и не равна -1 => будет изменена на возвращаемый код
	 * @param string $uri
	 * @param bool $headers
	 * @return string $response
	*/
	function get($uri, &$status_code=-1) {

		$process = curl_init();
		curl_setopt($process, CURLOPT_HEADER, 0);
		curl_setopt($process, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)");
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($process, CURLOPT_TIMEOUT, 20);
		curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);


		
		if (count($this->proxy_list) > 0){

			//Берем очередную проксю
			$proxy = $this->getNextProxy();

			echo "proxy use: {$proxy['ip']}:{$proxy['port']} {$proxy['user']} {$proxy['pass']}\n";
			curl_setopt($process, CURLOPT_PROXY, "{$proxy['ip']}:{$proxy['port']}");
			curl_setopt($process, CURLOPT_PROXYUSERPWD, "{$proxy['user']}:{$proxy['pass']}");
		}

		curl_setopt($process, CURLOPT_URL, $uri);
		$out = curl_exec($process); 
		

		//Нужно вернут еще и код
		if($status_code != -1) {
			$status_code = curl_getinfo($process, CURLINFO_HTTP_CODE);
		}
		
		curl_close($process);

		return $out;
		
	}

	function post($uri, $data, $headers=[]) {

		$process = curl_init();
		curl_setopt($process, CURLOPT_HEADER, 0);
		curl_setopt($process, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)");
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($process, CURLOPT_TIMEOUT, 20);
		curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($process, CURLOPT_POST, true);

		if (count($headers) > 0)
			curl_setopt($process, CURLOPT_HTTPHEADER, $headers);


		
		if (count($this->proxy_list) > 0){

			//Берем очередную проксю
			$proxy = $this->getNextProxy();

			echo "proxy use: {$proxy['ip']}:{$proxy['port']} {$proxy['user']} {$proxy['pass']}\n";
			curl_setopt($process, CURLOPT_PROXY, "{$proxy['ip']}:{$proxy['port']}");
			curl_setopt($process, CURLOPT_PROXYUSERPWD, "{$proxy['user']}:{$proxy['pass']}");
		}

		curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		curl_setopt($process, CURLOPT_URL, $uri);

		$out = curl_exec($process); 
		curl_close($process);
		return $out;

	}
}

?>