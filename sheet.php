<?php

/*
	Тут храним все для работы с листами.
  
 */

namespace Parser;

include_once __DIR__ . '/logs.php';


class Sheet
{
	private $id; //ID листа с которым работаем
	private $service; //Экзепляр для управления таблицами
	private $logs; //Экземпляр для логов
	private $timeout; //Задеркжа между выполнениями

	/**
	 * Передаем айди нашего листа и экземпляр для управления таблицами
	 * @param string $id
	 * @param object $service
	 * @param int $timeout
	*/
	function __construct($service, $id, $path_logs, $timeout=1)
	{
		$this->id = $id;
		$this->service = $service;
		$this->timeout = $timeout;

		$this->logs = new \Parser\Logs($path_logs, "G Sheet");
	}

	/**
	 * Меняем название нашего листа
	 * @param string $id
	 * @return object
	*/
	function changeTitle($title) {

		sleep($this->timeout); //Задержка перед запросом

		$requests = [
		    new \Google_Service_Sheets_Request([
		        'updateSpreadsheetProperties' => [
		            'properties' => [
		                'title' => $title
		            ],
		            'fields' => 'title'
		        ]
		  ])
		];

		$batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
		      'requests' => $requests
		]);

		$response = $this->service->spreadsheets->batchUpdate($this->id, $batchUpdateRequest);
		return $response;

	}

	/**
	 * Возвращает значения в заданном диапазоне
	 * @param string $range
	 * @return array
	*/

	function getValues($range) {
		sleep($this->timeout); //Задержка перед запросом

		$response = $this->service->spreadsheets_values->get($this->id, $range);
		return $response->getValues();
	}

	/**
	 * Обновить значения в заданном диапазоне на заданные.
	 * @param array data - где каждый массив содержит поле range - где мы обновляем
	 * и поле data - какие данные в этом диапазоне будут
	*/
	function updateValues($data) {

		try {

			foreach ($data as $item) {

				sleep($this->timeout);

				$range = $item['range'];
				$data = $item['data'];

				echo "now update range: {$range} \n";
				$this->logs->addMessage("update range: {$range}");
				// foreach ($data as $row) {
				// 	echo "{$row[0]} {$row[1]} {$row[2]} {$row[3]} \n";
				// }
				// echo "\n-------\n";		
					
				$requestBody = new \Google_Service_Sheets_ValueRange([
		    		'values' => $data
				]);
				$response = $this->service->spreadsheets_values->update($this->id, $range, $requestBody, ['valueInputOption' => 'USER_ENTERED']);

				
			}
		}
		catch (\Exception $e) {
			echo "new Exception: " . $e->getMessage() . "\n";
			$this->logs->addMessage("error update range: {$range}. Error: " . $e->getMessage());
		}

	}


}

?>