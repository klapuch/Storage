<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiHaving implements Clause, Having {
	private $clause;
	private $condition;

	public function __construct(Clause $clause, string $condition) {
		$this->clause = $clause;
		$this->condition = $condition;
	}

	public function orderBy(array $orders): OrderBy {
		return new AnsiOrderBy($this, $orders);
	}

	public function limit(int $limit): Limit {
		return new AnsiLimit($this, $limit);
	}

	public function offset(int $offset): Offset {
		return new AnsiOffset($this, $offset);
	}

	public function sql(): string {
		return sprintf('%s HAVING %s', $this->clause->sql(), $this->condition);
	}

}