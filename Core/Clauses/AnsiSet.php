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
			'%s %s',
			$this->withKeyword($this->clause->sql()),
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

	/**
	 * SQL with "SET" or "," based on previous SQL
	 * @return string
	 */
	private function withKeyword(string $sql): string
	{
		return $sql . ($this->continuing($sql) ? ',' : ' SET');
	}

	/**
	 * Was the previous SQL UPDATE?
	 * @param string $sql
	 * @return bool
	 */
	private function continuing(string $sql): bool
	{
		return (bool) preg_match('~UPDATE\s.+\sSET~', $sql);
	}
}