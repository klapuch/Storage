<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

interface Conversion {
	/**
	 * Converted value
	 *
	 * @return mixed
	 */
	public function value();
}