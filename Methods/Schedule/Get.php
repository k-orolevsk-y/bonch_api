<?php
	namespace Me\Korolevsky\BonchApi\Methods\Schedule;

	use Exception;
	use PHPHtmlParser\Dom;
	use JetBrains\PhpStorm\NoReturn;
	use Me\Korolevsky\BonchApi\Index;
	use Me\Korolevsky\BonchApi\Constants;
	use Me\Korolevsky\BonchApi\Interfaces\Method;

	class Get implements Method {

		#[NoReturn]
		public function __construct(array $request) {
			Index::parametersValidator([ 'group_id' ], $request);

			if($request['date'] != null) {
				if(!strtotime($request['date'])) Index::generateResponse([ 1, Constants::getErrors('parameters_error', [ 'date is incorrect.' ]) ]);

				$date = date('Y-m-d', strtotime($request['date']));
			} else {
				$date = date('Y-m-d');
			}

			$dom = new Dom();
			$url = "https://www.sut.ru/studentu/raspisanie/raspisanie-zanyatiy-studentov-ochnoy-i-vecherney-form-obucheniya?group=${request['group_id']}&date=$date";

			try {
				$dom->loadFromUrl($url);
			} catch(Exception) {
				Index::generateResponse([ Constants::getErrors('server_error'), Constants::getErrors('server_error', [ 'parsing turned to failure.' ]) ]);
			}

			$days = $dom->find('.vt237');
			foreach($days as $day) {
				$day_id = (int) $day->getAttribute('data-i');
				if($day_id == 0) continue;

				$dateN = explode(' ', $day->innerHtml)[1];
				if($dateN == date('d.m', strtotime($date))) {
					$id = $day_id;
				}
			}
			if($id === null) Index::generateResponse(returns: [ 'count' => 0, 'items' => [] ]);


			$schedule_all = $dom->find('.vt244');
			$items = [];

			foreach($schedule_all as $item) {
				if($item->innerHtml === "") continue;
				elseif($item->getAttribute('class') == "vt244 vt244a") continue;

				$time = explode('<br />', $item->find('.vt239')[0]);
				$in_schedule = [
					'num' => (int) $item->find('.vt283')[0]->innerHtml,
					'start' => date('H:i', strtotime($time[1])),
					'end' => date('H:i', strtotime(strip_tags($time[2]))),
					'classes' => [],
				];

				$schedule = $item->find(".rasp-day$id");
				foreach($schedule as $classes) {
					$classes = $classes->find('.vt258');
					foreach($classes as $class) {
						$name = trim($class->find('.vt240')->innerHtml);
						$teacher = trim($class->find('.teacher')[0]->innerHtml);
						$type = trim($class->find('.vt243')[0]->innerHtml);
						$audience = trim($class->find('.vt242')[0]->innerHtml);

						$navigator = null;
						if($audience != "ауд.: ДОТ") {
							$split = explode(';', $audience);
							$num = (int) filter_var($split[0], FILTER_SANITIZE_NUMBER_INT);
							$build_info = explode('/', ($split[1] ?? ""));

							if($num > 0 && trim($build_info[0]) == "Б22" && $build_info[1] > 0) {
								$navigator = "https://nav.sut.ru/?cab=k${build_info[1]}-$num";
							}
						}

						$in_schedule['classes'][] = [
							'name' => $name,
							'teacher' => $teacher,
							'type' => $type,
							'audience' => $audience,
							'navigator' => $navigator,
						];
					}
				}
				if($in_schedule['classes'] == null) continue;

				$items[] = $in_schedule;
			}

			Index::generateResponse(returns: [ 'count' => count($items), 'items' => $items ]);
		}

	}