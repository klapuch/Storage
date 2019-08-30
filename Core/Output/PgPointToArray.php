<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

final class PgPointToArray implements Conversion {
	/** @var string */
	private $original;

	/** @var string */
	private $type;

	/** @var \Klapuch\Storage\Output\Conversion */
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