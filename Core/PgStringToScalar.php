<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgStringToScalar implements Conversion {
	/** @var string|null */
	private $original;

	/** @var string */
	private $type;

	public function __construct(?string $original, string $type) {
		$this->original = $original;
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if ($this->original === null) {
			return $this->original;
		}
		switch (strtolower($this->type)) {
			case 'integer':
			case 'int':
			case 'smallint':
			case 'bigint':
				return self::toInt($this->original);
			case 'bool':
			case 'boolean':
				return self::toBool($this->original);
			default:
				return $this->original;
		}
	}

	private static function toInt(string $original): int {
		return (int) $original;
	}

	private static function toBool(string $original): bool {
		return $original === 't';
	}
}