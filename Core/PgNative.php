<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgNative implements Conversion {
	private $original;

	public function __construct(string $original) {
		$this->original = $original;
	}

	public function value(): string {
		return $this->original;
	}
}