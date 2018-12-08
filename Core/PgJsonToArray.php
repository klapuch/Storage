<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgJsonToArray implements Conversion {
	private $original;
	private $type;
	private $delegation;

	public function __construct(string $original, string $type, Conversion $delegation) {
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	public function value(): array {
		if (strcasecmp($this->type, 'json') === 0)
			return json_decode($this->original, true);
		return $this->delegation->value();
	}
}