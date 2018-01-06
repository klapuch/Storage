<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgIntRangeToArray implements Conversion {
	private $original;

	public function __construct(string $original) {
		$this->original = $original;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		[$left, $right] = [substr($this->original, 0, 1), substr($this->original, -1)];
		return array_merge(
			array_map('intval', explode(',', trim($this->original, $left . $right))),
			[$left, $right]
		);
	}
}