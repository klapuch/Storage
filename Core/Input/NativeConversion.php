<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Input;

final class NativeConversion implements Conversion {
	/** @var mixed */
	private $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function value(): string {
		if (is_bool($this->value)) {
			return $this->value ? 't' : 'f';
		}
		return (string) $this->value;
	}
}