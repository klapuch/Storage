<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgPointToArray implements Conversion {
	private $original;

	public function __construct(string $original) {
		$this->original = $original;
	}

	/**
	 * @return float[]
	 */
	public function value(): array {
		return array_combine(
			['x', 'y'],
			array_map(
				function(string $point): float {
					return floatval($point);
				},
				explode(',', trim($this->original, '()'))
			)
		);
	}
}