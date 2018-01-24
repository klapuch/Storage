<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiSet implements Set {
	private $clause;
	private $values;

	public function __construct(Clause $clause, array $values) {
		$this->clause = $clause;
		$this->values = $values;
	}

	public function where(string $comparison): Where {
		return new AnsiWhere($this, $comparison);
	}

	public function sql(): string {
		return sprintf(
			'%s SET %s',
			$this->clause->sql(),
			implode(
				', ',
				array_map(
					function(string $column, string $order): string {
						return sprintf('%s = %s', $column, $order);
					},
					array_keys($this->values),
					$this->values
				)
			)
		);
	}
}