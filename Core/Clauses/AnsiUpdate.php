<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiUpdate implements Update {
	private $table;

	public function __construct(string $table) {
		$this->table = $table;
	}

	public function set(array $values): Set {
		return new AnsiSet($this, $values);
	}

	public function sql(): string {
		return sprintf('UPDATE %s', $this->table);
	}

}