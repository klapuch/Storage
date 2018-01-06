<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class FakeConversion implements Conversion {
	private $value;

	public function __construct($value = null) {
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		return $this->value;
	}
}