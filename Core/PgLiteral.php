<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgLiteral implements Conversion {
	private $original;

	public function __construct($original) {
		$this->original = $original;
	}

	public function value(): string {
		if (is_string($this->original)) {
			return sprintf('\'%s\'', $this->original);
		} elseif (is_bool($this->original)) {
			return (new self($this->original ? 'true' : 'false'))->value();
		} elseif ($this->original === null) {
			return (new self('null'))->value();
		}
		return (new self(strval($this->original)))->value();
	}
}