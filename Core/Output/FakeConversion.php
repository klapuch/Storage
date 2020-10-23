<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

final class FakeConversion implements Conversion {
	/** @var mixed */
	private $value;

	/**
	 * @param mixed $value
	 */
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
