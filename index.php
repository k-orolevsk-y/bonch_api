<?php
	namespace Me\Korolevsky\BonchApi;
	require 'Autoload.php';

	// Отключает вывод каких-либо ошибок, сделано для релизной версии
	ini_set("display_errors", "0");
	error_reporting(0);

	use JetBrains\PhpStorm\NoReturn;

	class Index {

		/**
		 * Index constructor.
		 * (Главный конструктор, вызывается при каждом обращении к API.)
		 */
		#[NoReturn]
		public function __construct() {
			if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { // Если запрос от клиента типа OPTIONS
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Headers: *');
				header("HTTP/1.1 200 OK");
				die('ok'); // Отдаем header-параметры и отвечаем что все ОК
			}

			set_exception_handler([$this, 'exceptionHandler']); // Ставим кастомный обработчик исключений, если произойдет внеплановая ошибка в коде.

			$data = $this->getData(); // Получаем совмещенные POST и GET параметры
			$array = (array) explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]); // Т.к. через .htaccess у нас идет редирект на index.php, таким извращением мы достаем название метода
			$method = array_pop($array); // Т.к. в explode будет массив, то нам надо достать последний элемент (кстати также это приводит к рекурсии через / в ссылке)

			unset($_GET['method']);
			unset($_POST['method']);

			if($data['method'] != null && $data['method'] != $method && $method != null) { // Если одновременнно обращаются к разным методам через ссылку и через параметр выдаём ошибку
				$_SERVER['REQUEST_URI'] = $data['method'];

				Index::generateResponse([ Constants::getErrorsKey('invalid_request'), Constants::getErrors('invalid_request', [ 'this method can not be called that way' ])  ]);
			} elseif($method == null) { // Если в ссылке нет метода, проверяем в параметрах
				$_SERVER['REQUEST_URI'] = $data['method'];

				$array = (array) explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
				$method = array_pop($array);
			}


			if(mb_strcut($method, strlen($method) -4) == '.php') $method = mb_strcut($method, 0, -4); // Для умников, которые заходят обратиться к .php файлу
			if($method == null) Index::generateResponse([ Constants::getErrorsKey('error_method'), Constants::getErrors('error_method', [ '`method` field cannot be empty' ]) ]); // Если не удалось разными способами найти метод, выводим ошибку

			$required_method = Constants::getMethod($method); // Пробуем найти класс метода в константах
			if($required_method == null) { // Если класса нет в константах, то попробуем найти файл сами
				$method_explode = [];
				foreach(explode('.', $method) as $str)
					$method_explode[] = ucfirst($str);
				$required_method = implode('/', $method_explode);

				if(!file_exists('Methods/' . $required_method . '.php')) // Если файла не существует, выводим ошибку об неверном методе
					Index::generateResponse([ Constants::getErrorsKey('error_method'), Constants::getErrors('error_method', [ 'method `' . $method . '` does not exists' ]) ]);
			}

			require "Methods/{$required_method}.php"; // Подгружаем файл с классом метода
			$new_method = 'Me\Korolevsky\BonchApi\Methods\\' . str_replace('/', '\\', $required_method); // Создаем обращение к классу через пространство имён
			new $new_method($data); // Создаем экземпляр класса метода
		}

		/**
		 * Getting merged POST and GET request.
		 *
		 * @return array
		 */
		public static function getData(): array {
			return array_change_key_case($_POST + $_GET, CASE_LOWER);
		}

		/**
		 * Generate response of API.
		 *
		 * @param array|null $error
		 * @param mixed $returns
		 */
		#[NoReturn]
		public static function generateResponse(?array $error = null, mixed $returns = []) {
			header('Access-Control-Allow-Origin: *');
			header('Content-Type: application/json');
			$response = [];

			if($error != null) {
				$response['ok'] = false;
				$response['error']['error_code'] = (int) $error[0];
				$response['error']['error_msg'] = $error[1];

				$error = array_splice($error, 2);
				if($error[0] != null) {
					$response['error']['info']['lang'] = $error[0];
					$error = array_splice($error, 1);
				}

				foreach($error as $key => $value) $response['error'][$key] = $value;

				$response['error']['request_params'] = [];

				$array = (array) explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
				$method = array_pop($array);
				array_push($response['error']['request_params'], [ 'key' => 'method', 'value' =>  $method == '' ? null : $method ]);

				$data = Index::getData();
				unset($data['access_token']);

				foreach($data as $key => $value) array_push($response['error']['request_params'], [ 'key' => $key, 'value' => $value ]);
				$response['error']['request_params'][] = [ 'key' => 'time', 'value' => time() ];
			} else {
				$response['ok'] = true;
				$response['time'] = time();

				if(is_array($returns)) foreach($returns as $key => $value) $response['response'][$key] = $value;
				else $response['response'] = $returns;
			}

			exit(json_encode($response));
		}

		/**
		 * Parameters validator of request.
		 *
		 * @param array $params
		 * @param array $request
		 */
		#[NoReturn]
		public static function parametersValidator(array $params, array $request) {
			$params = str_replace(' ', '_', $params);

			if( ( $missed = array_diff($params, array_keys( array_diff( $request, [null] ) ) ) ) != null )
				Index::generateResponse([ Constants::getErrorsKey('parameters_error'), Constants::getErrors('parameters_error', [ array_shift($missed) . ' a required parameter!' ]) ]);
		}

		/**
		 * Auto set type to var.
		 *
		 * @param array|null $array $array
		 *
		 * @return array|null
		 */
		public static function autoTypeConversion(?array $array): ?array {
			if($array === null) return null;

			foreach($array as $key => $val) {
				if(is_array($val) || is_object($val)) $array[$key] = Index::autoTypeConversion( (array) $val );
				elseif(is_bool($val)) $array[$key] = boolval($val);
				else {
					$val_num = str_replace(',', '.', $val);
					if(is_numeric($val_num)) {
						if(strstr($val_num, '.') !== false) $array[$key] = floatval($val);
						else $array[$key] = intval($val_num);
					}
				}
			}

			return $array;
		}

		/**
		 * Get user ip by cloudflare.
		 * (Т.к. мой сервер прячется за CloudFlare, то получаем IP пользователя подобным способом.)
		 *
		 * @return string
		 */
		public static function getUserIp(): string {
			if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else if(isset($_SERVER['REMOTE_ADDR'])) {
				return $_SERVER['REMOTE_ADDR'];
			}

			return "unknown";
		}

		/**
		 * Getting the ending for a word depending on the number
		 *
		 * @param $number
		 * @param array $after
		 *
		 * @return string
		 */
		public static function pluralForm($number, array $after): string {
			$num = $number;
			if(strstr($number, '.') !== false) $number = explode('.', $number)[1];

			$cases = array (2, 0, 1, 1, 1, 2);
			return $num.' '.$after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
		}

		/**
		 * Exception handler.
		 * @param $exception
		 */
		#[NoReturn]
		public function exceptionHandler($exception) {
			if(Index::getData()['debug'] != null) {
				Index::generateResponse([ Constants::getErrorsKey('server_error'), Constants::getErrors('server_error', [ $exception->getMessage() ]), 'debug' => [ 'trace' => $exception->getTrace() ] ]);
			} else {
				Index::generateResponse([ Constants::getErrorsKey('server_error'), 'Internal server error.' ]);
			}
		}
	}

	new Index();