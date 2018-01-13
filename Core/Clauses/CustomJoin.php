<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class CustomJoin implements Clause, Join {
	private $clause;
	private $type;
	private $table;
	private $condition;

	public function __construct(Clause $clause, string $type, string $table, string $condition) {
		$this->clause = $clause;
		$this->type = $type;
		$this->table = $table;
		$this->condition = $condition;
	}

	public function where(string $comparison): Where {
		return new AnsiWhere($this, $comparison);
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
		return sprintf('%s %s JOIN %s ON %s', $this->clause->sql(), $this->type, $this->table, $this->condition);
	}
}