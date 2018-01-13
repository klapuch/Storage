<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiWhere implements Where {
	private $condition;
	private $clause;

	public function __construct(Clause $clause, string $condition) {
		$this->condition = $condition;
		$this->clause = $clause;
	}

	public function andWhere(string $condition): ChainedWhere {
		return new ConjunctWhere($this, 'AND', $condition);
	}

	public function orWhere(string $condition): ChainedWhere {
		return new ConjunctWhere($this, 'OR', $condition);
	}

	public function groupBy(array $columns): GroupBy {
		return new AnsiGroupBy($this, $columns);
	}

	public function having(string $condition): Having {
		return new AnsiHaving($this, $condition);
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
		return sprintf('%s WHERE %s', $this->clause->sql(), $this->condition);
	}
}