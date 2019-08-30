<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Input;

interface Conversion {
	/**
	 * Converted value
	 */
	public function value(): string;
}