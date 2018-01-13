<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiSelect implements Clause, Select {
	private $columns;

	public function __construct(array $columns) {
		$this->columns = $columns;
	}

	public function from(array $tables): From {
		return new AnsiFrom($this, $tables);
	}

	public function sql(): string {
		return sprintf('SELECT %s', implode(', ', $this->columns));
	}
}