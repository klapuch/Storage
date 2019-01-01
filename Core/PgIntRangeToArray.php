<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgIntRangeToArray implements Conversion {
	/** @var string */
	private $original;

	/** @var string */
	private $type;

	/** @var \Klapuch\Storage\Conversion */
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
		if (strcasecmp($this->type, 'int4range') === 0) {
			[$left, $right] = [substr($this->original, 0, 1), substr($this->original, -1)];
			return array_merge(
				array_map('intval', explode(',', trim($this->original, $left . $right))),
				[$left, $right]
			);
		}
		return $this->delegation->value();
	}
}