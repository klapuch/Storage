<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

final class PgText implements Conversion {
	private string $original;

	private string $type;

	private Conversion $delegation;

	public function __construct(string $original, string $type, Conversion $delegation) {
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if (strcasecmp('text', $this->type) === 0) {
			return $this->original;
		}
		return $this->delegation->value();
	}
}
