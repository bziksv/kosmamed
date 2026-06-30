<?php
namespace Rbs\Moysklad;

class Utils
{
	
	public static function build_exception_message(\Throwable $e)
	{
		$message = $e->getMessage();
		$file = $e->getFile();
		$line = $e->getLine();

		$moduleDir = dirname(__DIR__, 2);
		
		if(mb_strpos($file, $moduleDir) !== false) {
			return $message;
		}

		return $message . " [{$file}:{$line}]";
	}

	public static function parse_href(string $href)
	{
		preg_match('/entity\/([^\/]+)\/[a-f0-9\-]+/', $href, $matches);
		if (isset($matches[1])) {
			preg_match('/[a-f0-9\-]+$/', $href, $idMatches);
			return [
				'entity' => $matches[1],
				'id' => $idMatches[0]
			];
		}
		return null;
	}

	public static function array_to_object($defs): object
	{
		$innerfunc = function ($a) use (&$innerfunc) {
			if (is_array($a) && isset($a[0])) {
				$result = [];
				foreach ($a as $b) {
					if (is_array($b)) {
						$result[] = (object)array_map($innerfunc, $b);
					} else {
						$result[] = $b;
					}
				}
				return $result;
			}

			return (is_array($a)) ? (object)array_map($innerfunc, $a) : $a;
		};

		return (object)array_map($innerfunc, $defs);
	}

	public static function object_to_array($input): array
	{
		$result = [];
		
		if(is_object($input) && self::is_success($input)) {
			$result = (array)$input;
		} else if(gettype($input) === 'array') {
			$result = $input;
		}

		foreach (['hasErrors', 'isNull', 'errors', 'attempts'] as $field) {
			if (isset($result[$field])) {
				unset($result[$field]);
			}
		}

		return $result;
	}

	public static function get_date_string(): string
	{
		$culture = \Bitrix\Main\Context::getCurrent()->getCulture();
		$phpDateTime = new \DateTime();
		$dateTime = \Bitrix\Main\Type\DateTime::createFromPhp($phpDateTime);
		return $dateTime->toString($culture);
	}

	public static function get_date_ms_string(string $dateBx = '', bool $withTime = false, $isEuMskTimezone = false): string
	{
		$timeZone = date_default_timezone_get();
		if ($isEuMskTimezone) {
			$timeZone = 'Europe/Moscow';
		}
		$phpDateTime = new \DateTime($dateBx, new \DateTimeZone($timeZone));
		if ($withTime) {
			return $phpDateTime->format('Y-m-d H:i:s');
		} else {
			return $phpDateTime->format('Y-m-d 00:00:00.000');
		}
	}

	public static function count($var = null): int
	{
		if (is_array($var)) {
			return count($var);
		}
		return 0;
	}

	public static function is_count($var = null): bool
	{
		return self::count($var) > 0;
	}

	public static function has_errors($object = null): bool
	{
		$hasErrors = false;
		if (self::property_exists($object, ['hasErrors'])) {
			$hasErrors = boolval($object->hasErrors);
		} else if(self::array_exists($object, 'errors')) {
			$hasErrors = true;
		}
		return $hasErrors;
	}

	public static function is_success($object = null): bool
	{
		return !self::has_errors($object);
	}

	public static function property_exists($object = null, $propPath = []): bool
	{
		if (is_object($object) && self::count($propPath) > 0) {
			$currentProp = array_shift($propPath);
			if (property_exists($object, $currentProp)) {
				if (self::count($propPath) > 0) {
					return self::property_exists($object->{$currentProp}, $propPath);
				}
				return true;
			}
		}
		return false;
	}

	public static function array_exists($object = null, $arrayProp = 'rows'): bool
	{
		if (self::property_exists($object, [$arrayProp])) {
			return self::is_count($object->{$arrayProp});
		}
		return false;
	}

	public static function send_bx_event(string $moduleId = '', string $eventName = '', array $eventParams = [], &$varIfSuccess = null)
	{
		$event = new \Bitrix\Main\Event($moduleId, $eventName, $eventParams);
		$event->send();
		if ($varIfSuccess !== null) {
			if ($event->getResults()) {
				foreach ($event->getResults() as $eventResult) {
					if ($eventResult->getType() === \Bitrix\Main\EventResult::SUCCESS) {
						$varIfSuccess = $eventResult->getParameters();
					}
				}
			}
		}
	}

	public static function is_exsists_cache($cacheId = '', $cachePath = '', $cacheTime = 5)
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if ($cache->initCache($cacheTime, $cacheId, Config::getCachePath($cachePath))) {
			return true;
		} else if ($cache->startDataCache()) {
			$cache->endDataCache(time());
			return false;
		}
	}

	public static function say_ok()
	{
		$session = \Bitrix\Main\Application::getInstance()->getSession();
		if ($session->isStarted()) {
			$session->save();
		}

		if (is_callable('fastcgi_finish_request')) {
			fastcgi_finish_request();
			return;
		}

		ignore_user_abort(true);

		ob_start();
		$serverProtocole = !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : filter_input(INPUT_SERVER, 'SERVER_PROTOCOL');
		header($serverProtocole . ' 200 OK');
		header('Content-Encoding: none');
		header('Content-Length: ' . ob_get_length());
		header('Connection: close');
		ob_end_flush();
		ob_flush();
		flush();
	}

	public static function build_time_interval_array(): array
	{
		$timeIntervalsForFullUpd = [];
		for ($i = 0; $i <= 23; $i++) {
			$startTime = $i > 9 ? (string)$i : '0' . $i;
			$finishTime = ($i + 1 > 9) ? (string)($i + 1) : '0' . (string)($i + 1);
			$finishTime = (int)$finishTime === 24 ? '00' : $finishTime;
			$timeIntervalsForFullUpd[$i] = $startTime . ':00' . ' - ' . $finishTime . ':00';
		}
		return $timeIntervalsForFullUpd;
	}

	public static function build_number_array(int $start = 100, int $finish = 1000, int $step = 100): array
	{
		$result = [];

		if ($start > $finish || $start <= 0 || $finish <= 0 || $step <= 0) {
			$start = 100;
			$finish = 1000;
			$step = 100;
		}

		for ($i = $start; $i <= $finish; $i += $step) {
			$result[$i] = $i;
		}

		return $result;
	}

	public static function get_human_filesize(int $bytes = 0, int $decimals = 2)
	{
		$sz = 'BKMGTP';
		$factor = floor((mb_strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	public static function get_dir_size(string $path = ''): int
	{
		$returnSize = 0;

		if (!$h = opendir($path)) return $returnSize;

		while (($element = readdir($h)) !== false) {
			if ($element != "." && $element != "..") {

				$all_path = $path . "/" . $element;

				if (filetype($all_path) == "file") {
					$returnSize += filesize($all_path);
				} elseif (filetype($all_path) == "dir") {
					$returnSize += self::get_dir_size($all_path);
				}
			}
		}

		closedir($h);

		return (int)$returnSize;
	}

	public static function delete_tree(string $dir = '')
	{
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? self::delete_tree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

}
