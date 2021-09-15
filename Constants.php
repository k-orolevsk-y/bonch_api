<?php
	namespace Me\Korolevsky\BonchApi;

	class Constants {
		private const ERRORS = [
			'error_not_found' => 'The requested API error/lang (%s) was not found! Report this bug by email: i@korolevsky.me',
			'authorization_failed' => 'Authorization failed: %s',
			'error_method' => 'Error getting method: %s',
			'parameters_error' => 'One of the parameters specified was missing or invalid: %s',
			'invalid_request' => 'Invalid request: %s',
			'server_error' => 'Server error: %s'
		];

		private const ERRORS_KEY = [
			'authorization_failed' => 913,
			'error_method' => 901,
			'parameters_error' => 902,
			'invalid_request' => 903,
			'server_error' => 904
		];

		private const LANG = [];

		private const METHODS  = [];

		/**
		 * So, we can prohibit submit an instance of the class.
		 */
		private function __construct() {}

		/**
		 * Function to obtain a description of the error with the replacement function.
		 *
		 * @param string $key
		 * @param array|null $replacement
		 *
		 * @return string
		 */
		public static function getErrors(string $key, ?array $replacement = null): string {
			$error = static::ERRORS[$key];
			if($error == null) return Constants::getErrors('error_not_found', [ $key ]);

			if($replacement != null) foreach($replacement as $value) $error = preg_replace('/%s/', $value, $error, 1);

			return $error;
		}

		/**
		 * Function for receiving an error code from constant.
		 *
		 * @param string $key
		 *
		 * @return int
		 */
		public static function getErrorsKey(string $key): int {
			$error_key = static::ERRORS_KEY[$key];
			if($error_key == null) return 0;

			return $error_key;
		}

		/**
		 * Function for receiving a method from constant.
		 *
		 * @param string $method
		 *
		 * @return string|null
		 */
		public static function getMethod(string $method): ?string {
			return static::METHODS[$method];
		}

		/**
		 * Function for obtaining a description with function.
		 *
		 * @param string $key
		 * @param array|null $replacement
		 *
		 * @return string
		 */
		public static function getLang(string $key, ?array $replacement = null): string {
			$lang = static::LANG[$key];
			if($lang == null) return Constants::getErrors('error_not_found', [ $key ]);
			if($replacement != null) foreach($replacement as $value) $lang = preg_replace('/%s/', $value, $lang, 1);

			return $lang;
		}


	}