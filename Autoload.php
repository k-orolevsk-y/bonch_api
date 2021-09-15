<?php
	namespace Me\Korolevsky\BonchApi;


	class Autoload {

		public function __construct() {
			require "vendor/autoload.php"; /** Import composer autoload file. */
			self::registerAutoload(); /** Register my handler autoload files. */
		}

		private function registerAutoload() {
			spl_autoload_register(function(string $class_name) {
				$array = explode('\\', $class_name);
				$class_name = array_pop($array);
				$namespace = $array[count($array)-1];

				if(file_exists("${class_name}.php") && $namespace == "BonchApi") { /** Maybe it's just file? */
					@require "${class_name}.php";
				} elseif(file_exists("Interfaces/${class_name}.php") && $namespace == "Interfaces") { /** Maybe it's interface file? */
					@require "Interfaces/${class_name}.php";
				}
			});
		}

	}

	new Autoload();