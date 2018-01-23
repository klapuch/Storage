<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

use Klapuch\Storage;

final class PgSet implements Set {
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
		return (new AnsiSet(
			$this->clause,
			array_map(
				function($value): string {
					return (new Storage\PgLiteral($value))->value();
				},
				$this->values
			)
		))->sql();
	}
}