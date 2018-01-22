<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class Returning implements Clause {
	private $clause;
	private $columns;

	public function __construct(Clause $clause, array $columns) {
		$this->clause = $clause;
		$this->columns = $columns;
	}

	public function sql(): string {
		return sprintf(
			'%s RETURNING %s',
			$this->clause->sql(),
			implode(', ', $this->columns)
		);
	}
}