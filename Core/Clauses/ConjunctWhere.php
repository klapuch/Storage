<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class ConjunctWhere implements ChainedWhere {
	private $condition;
	private $clause;
	private $conjunct;

	public function __construct(Clause $clause, string $conjunct, string $condition) {
		$this->condition = $condition;
		$this->clause = $clause;
		$this->conjunct = $conjunct;
	}

	public function where(string $condition): ChainedWhere {
		return new self($this, 'AND', $condition);
	}

	public function orWhere(string $condition): ChainedWhere {
		return new self($this, 'OR', $condition);
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
		return sprintf('%s %s %s', $this->clause->sql(), $this->conjunct, $this->condition);
	}
}