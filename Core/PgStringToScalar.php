<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgStringToScalar implements Conversion {
	private const TYPES = [
		'integer' => 'toInt',
		'int' => 'toInt',
		'smallint' => 'toInt',
		'boolean' => 'toBool',
		'bool' => 'toBool',
	];
	private $original;
	private $type;

	public function __construct(?string $original, string $type) {
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

	// @codingStandardsIgnoreStart Used by call_user_func_array
	private function toInt(string $original): int {
		return intval($original);
	}

	private function toBool(string $original): bool {
		return $original === 't' ? true : false;
	}
	// @codingStandardsIgnoreEnd
}