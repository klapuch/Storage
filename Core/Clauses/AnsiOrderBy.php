<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiOrderBy implements Clause, OrderBy {
	private $clause;
	private $orders;

	public function __construct(Clause $clause, array $orders) {
		$this->clause = $clause;
		$this->orders = $orders;
	}

	public function limit(int $limit): Limit {
		return new AnsiLimit($this, $limit);
	}

	public function offset(int $offset): Offset {
		return new AnsiOffset($this, $offset);
	}

	public function sql(): string {
		return sprintf(
			'%s ORDER BY %s',
			$this->clause->sql(),
			implode(
				', ',
				array_map(
					function(string $column, string $order): string {
						return sprintf('%s %s', $column, $order);
					},
					array_keys($this->orders),
					$this->orders
				)
			)
		);
	}

}