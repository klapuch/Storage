<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiLimit implements Limit {
	private $clause;
	private $limit;

	public function __construct(Clause $clause, int $limit) {
		$this->clause = $clause;
		$this->limit = $limit;
	}

	public function offset(int $offset): Offset {
		return new AnsiOffset($this, $offset);
	}

	public function sql(): string {
		return sprintf('%s LIMIT %s', $this->clause->sql(), $this->limit);
	}

}