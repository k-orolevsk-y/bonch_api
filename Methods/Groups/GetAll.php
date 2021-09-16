<?php
	namespace Me\Korolevsky\BonchApi\Methods\Groups;

	use Exception;
	use PHPHtmlParser\Dom;
	use JetBrains\PhpStorm\NoReturn;
	use Me\Korolevsky\BonchApi\Index;
	use Me\Korolevsky\BonchApi\Constants;
	use Me\Korolevsky\BonchApi\Interfaces\Method;

	class GetAll implements Method {

		#[NoReturn]
		public function __construct(array $request) {
			$dom = new Dom();
			try {
				$dom->loadFromUrl("https://www.sut.ru/studentu/raspisanie/raspisanie-zanyatiy-studentov-ochnoy-i-vecherney-form-obucheniya");
			} catch(Exception) {
				Index::generateResponse([ Constants::getErrors('server_error'), Constants::getErrors('server_error', [ 'parsing turned to failure.' ]) ]);
			}

			$faculties = $dom->find('.vt252');
			if($request['name'] != null) {
				foreach($faculties as $faculty) {
					$name = trim($faculty->find('.vt253')[0]->innerHtml);
					similar_text(mb_strtolower($name), mb_strtolower($request['name']), $percent);
					if($percent >= 95) {
						$item = [
							'name' => trim($faculty->find('.vt253')[0]->innerHtml),
							'description' => trim($faculty->find('.vt254')[0]->innerHtml),
							'count' => 0,
							'groups' => []
						];

						$groups = $faculty->find('.vt256');
						foreach($groups as $group) {
							$item['groups'][] = [
								'id' => (int) $group->getAttribute('data-i'),
								'name' => trim($group->innerHtml)
							];
						}
						$item['count'] = count($item['groups']);

						Index::generateResponse(returns: $item);
					}
				}

				Index::generateResponse(returns: null);
			}

			$items = [];
			foreach($faculties as $faculty) {
				$item = [
					'name' => trim($faculty->find('.vt253')[0]->innerHtml),
					'description' => trim($faculty->find('.vt254')[0]->innerHtml),
					'count' => 0,
					'groups' => []
				];

				$groups = $faculty->find('.vt256');
				foreach($groups as $group) {
					$item['groups'][] = [
						'id' => (int) $group->getAttribute('data-i'),
						'name' => trim($group->innerHtml)
					];
				}

				$item['count'] = count($item['groups']);
				$items[] = $item;
			}

			Index::generateResponse(returns: [ 'count' => count($items), 'items' => $items ]);
		}

	}