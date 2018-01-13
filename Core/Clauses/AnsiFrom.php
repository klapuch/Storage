<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiFrom implements Clause, From {
	private $clause;
	private $tables;

	public function __construct(Clause $clause, array $tables) {
		$this->clause = $clause;
		$this->tables = $tables;
	}

	public function where(string $comparison): Where {
		return new AnsiWhere($this, $comparison);
	}

	public function join(string $type, string $table, string $condition): Join {
		return new CustomJoin($this, $type, $table, $condition);
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
		return sprintf('%s FROM %s', $this->clause->sql(), implode(', ', $this->tables));
	}
}