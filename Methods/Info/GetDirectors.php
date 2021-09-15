<?php
	namespace Me\Korolevsky\BonchApi\Methods\Info;

	use Exception;
	use PHPHtmlParser\Dom;
	use JetBrains\PhpStorm\NoReturn;
	use Me\Korolevsky\BonchApi\Index;
	use Me\Korolevsky\BonchApi\Constants;
	use Me\Korolevsky\BonchApi\Interfaces\Method;

	class GetDirectors implements Method {

		#[NoReturn]
		public function __construct(array $request) {
			$dom = new Dom();
			$url = "https://www.sut.ru/university/about/rukovodstvo";

			try {
				$dom->loadFromUrl($url);
			} catch(Exception) {
				Index::generateResponse([ Constants::getErrors('server_error'), Constants::getErrors('server_error', [ 'parsing turned to failure.' ]) ]);
			}

			$directors = $dom->find(".teacher_card");
			$items = [];

			foreach($directors as $director) {
				$info = $director->find('.teacher_card-info');

				$name = str_replace('<br />', ' ', $info->find('h4 a')->innerHtml);
				$description = trim($info->find('p')->innerHtml);
				$img = "https://sut.ru".$director->find('.teacher_card-img img')[0]->getAttribute('src');
				$contact = ['tel' => [], 'email' => ''];
				$address = trim($info->find('div .vt61')[0]->innerHtml ?? $info->find('.vt128')[1]->find('.vt60a span')->innerHtml);
				$work_time = trim($info->find('.vt61')[1]->innerHtml);

				$contacts = $info->find('.vt58 a');
				foreach($contacts as $contact1) {
					$contact1 = $contact1->innerHtml;
					if(filter_var($contact1, FILTER_VALIDATE_EMAIL)) {
						$contact['email'] = $contact1;
					} else {
						$contact['tel'][] = $contact1;
					}
				}

				$items[] = [
					'name' => $name,
					'description' => $description,
					'img' => $img,
					'contact' => $contact,
					'address' => $address,
					'work_time' => $work_time
				];
			}

			Index::generateResponse(returns: [ 'count' => count($items), 'items' => $items  ]);
		}

	}