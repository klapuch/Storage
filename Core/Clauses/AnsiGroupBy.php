<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiGroupBy implements Clause, GroupBy {
	private $clause;
	private $columns;

	public function __construct(Clause $clause, array $columns) {
		$this->clause = $clause;
		$this->columns = $columns;
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
		return sprintf('%s GROUP BY %s', $this->clause->sql(), implode(', ', $this->columns));
	}

}