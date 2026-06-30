<?php

namespace Askaron\ClientId\Conversion;


class YMGenerator
{
	public function prepareDataToSend($arData)
	{
		$filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/conversion_csv/';
		$fileName = md5(json_encode($arData) . 'ASKARON') . '.csv';
		$handle = fopen($filePath . $fileName, 'w');
		$fields = ['ClientId', 'Target', 'DateTime'];
		fputcsv($handle, $fields, $delimiter = ",");
		foreach ($arData as $dataField){
			$fields = [$dataField['CLIENT_ID'], $dataField['TARGET'], $dataField['CONVERSION_DATE']->format('U')];
			$writeResult = fputcsv($handle, $fields, $delimiter = ",");
		}
		fclose($handle);
		if ($writeResult)
		{
			return $filePath . $fileName;
		}
		else
		{
			return false;
		}
	}

	public function sendDataToService($filePath,$siteId)
	{
		$result = '';
		if(function_exists('curl_init') !== false)
		{

			$counter = \Bitrix\Main\Config\Option::get('askaron.clientid', "ym_counter" . $siteId);
			$token = \Bitrix\Main\Config\Option::get('askaron.clientid', "ym_token" . $siteId);
			$client_id_type = "CLIENT_ID";
			$curl = curl_init("https://api-metrika.yandex.ru/management/v1/counter/$counter/offline_conversions/upload?client_id_type=$client_id_type");

			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, ['file' => new \CurlFile($filePath)]);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data", "Authorization: OAuth $token"]);

			$result = curl_exec($curl);
			curl_close($curl);
		}
		return $result;
	}

	public function submitConversion($data)
	{
		if (is_array($data) && count($data) > 0)
		{
			$csvFile = $this->prepareDataToSend($data);
			if (is_file($csvFile))
			{
				return $this->sendDataToService($csvFile, $data[array_rand($data)]['SITE_ID']);
			}
		}
	}
}