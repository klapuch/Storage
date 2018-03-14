<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgPointToArray implements Conversion {
	private $original;
	private $type;
	private $delegation;

	public function __construct(string $original, string $type, Conversion $delegation) {
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if (strcasecmp($this->type, 'point') === 0) {
			return array_combine(
				['x', 'y'],
				array_map('floatval', explode(',', trim($this->original, '()')))
			);
		}
		return $this->delegation->value();
	}
}