<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

final class PgNullable implements Conversion {
	private ?string $original;

	private Conversion $delegation;

	public function __construct(?string $original, Conversion $delegation) {
		$this->original = $original;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed|null
	 */
	public function value() {
		if ($this->original === null) {
			return $this->original;
		}
		return $this->delegation->value();
	}
}
