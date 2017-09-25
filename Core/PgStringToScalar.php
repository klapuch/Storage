<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgStringToScalar implements Conversion {
	private const AUTOMATIC = '_automatic';
	private const TYPES = [
		'integer' => 'toInt',
		'int' => 'toInt',
		'boolean' => 'toBool',
		'bool' => 'toBool',
		self::AUTOMATIC => 'cast',
	];
	private $original;
	private $type;

	public function __construct(?string $original, string $type = self::AUTOMATIC) {
		$this->original = $original;
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if ($this->original !== null && array_key_exists(strtolower($this->type), self::TYPES))
			return call_user_func_array([$this, self::TYPES[strtolower($this->type)]], [$this->original]);
		return $this->original;
	}

	private function toInt(string $original): int {
		return intval($original);
	}

	private function toBool(string $original): bool {
		return $original === 't' ? true : false;
	}

	// @codingStandardsIgnoreStart Used by call_user_func_array
	/**
	 * @return mixed
	 */
	private function cast(string $original) {
		if ($original === 't' || $original === 'f')
			return $this->toBool($original);
		elseif (is_numeric($original))
			return $this->toInt($original);
		return $original;
	}
	// @codingStandardsIgnoreEnd
}