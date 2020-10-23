<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

final class PgJsonToArray implements Conversion {
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
		if (strcasecmp($this->type, 'json') === 0 || strcasecmp($this->type, 'jsonb') === 0) {
			return json_decode($this->original, true, 512, JSON_THROW_ON_ERROR);
		}
		return $this->delegation->value();
	}
}
