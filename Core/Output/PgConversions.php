<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

final class PgConversions implements Conversion {
	private ?string $original;

	private string $type;

	public function __construct(?string $original, string $type) {
		$this->original = $original;
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		return (new PgNullable(
			$this->original,
			new PgText(
				$this->original,
				$this->type,
				new PgJsonToArray(
					$this->original,
					$this->type,
					new PgNative($this->original),
				),
			),
		))->value();
	}
}
