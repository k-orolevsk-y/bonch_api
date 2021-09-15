<?php
	namespace Me\Korolevsky\BonchApi\Interfaces;

	interface Method {
		/**
		 * Method constructor.
		 *
		 * @param array $request
		 */
		public function __construct(array $request);

	}