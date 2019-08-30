<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

final class PgNative implements Conversion {
	/** @var string */
	private $original;

	public function __construct(string $original) {
		$this->original = $original;
	}

	public function value(): string {
		return $this->original;
	}
}