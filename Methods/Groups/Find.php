<?php
	namespace Me\Korolevsky\BonchApi\Methods\Groups;

	use Exception;
	use PHPHtmlParser\Dom;
	use JetBrains\PhpStorm\NoReturn;
	use Me\Korolevsky\BonchApi\Index;
	use Me\Korolevsky\BonchApi\Constants;
	use Me\Korolevsky\BonchApi\Interfaces\Method;

	class Find implements Method {

		#[NoReturn]
		public function __construct(array $request) {
			Index::parametersValidator([ 'name' ], $request);

			$dom = new Dom();
			try {
				$dom->loadFromUrl("https://www.sut.ru/studentu/raspisanie/raspisanie-zanyatiy-studentov-ochnoy-i-vecherney-form-obucheniya");
			} catch(Exception) {
				Index::generateResponse([ Constants::getErrors('server_error'), Constants::getErrors('server_error', [ 'parsing turned to failure.' ]) ]);
			}

			$faculties = $dom->find('.vt252');
			foreach($faculties as $faculty) {
				$item = [
					'name' => trim($faculty->find('.vt253')[0]->innerHtml),
					'description' => trim($faculty->find('.vt254')[0]->innerHtml),
					'group' => []
				];

				$groups = $faculty->find('.vt256');
				foreach($groups as $group) {
					$name = trim($group->innerHtml);
					similar_text(mb_strtolower($name), mb_strtolower($request['name']), $percent);

					if($percent >= 95) {
						$item['group'] = [
							'id' => (int) $group->getAttribute('data-i'),
							'name' => $name
						];
						break;
					}
				}

				if($item['group'] != null) break;
			}

			if($item['group'] == null) {
				$item = null;
			}

			Index::generateResponse(returns: $item);
		}

	}